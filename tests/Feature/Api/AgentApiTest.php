<?php

namespace Tests\Feature\Api;

use App\Models\Device;
use App\Models\DeviceCall;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_agent_endpoint_requires_token()
    {
        $this->postJson('/api/agent/heartbeat')->assertUnauthorized();
        $this->postJson('/api/agent/ingest')->assertUnauthorized();
    }

    public function test_suspended_device_is_rejected()
    {
        $user = User::factory()->create();
        $device = Device::factory()->for($user)->create(['status' => 'suspended']);

        $this->withToken($device->agent_token)
            ->postJson('/api/agent/heartbeat')
            ->assertUnauthorized();
    }

    public function test_pending_device_activates_on_first_checkin()
    {
        $user = User::factory()->create();
        $device = Device::factory()->for($user)->create([
            'status' => 'pending',
            'consent_recorded' => true,
            'consented_at' => now()->subDay(),
        ]);

        $this->withToken($device->agent_token)
            ->postJson('/api/agent/heartbeat')
            ->assertOk();

        $device->refresh();
        $this->assertSame('active', $device->status);
        $this->assertNotNull($device->last_seen_at);
    }

    public function test_pending_device_without_consent_is_rejected()
    {
        $user = User::factory()->create();
        $device = Device::factory()->for($user)->create([
            'status' => 'pending',
            'consent_recorded' => false,
            'consented_at' => null,
        ]);

        $this->withToken($device->agent_token)
            ->postJson('/api/agent/heartbeat')
            ->assertUnauthorized();

        $this->assertSame('pending', $device->fresh()->status);
    }

    public function test_heartbeat_updates_device_metadata()
    {
        $user = User::factory()->create();
        $device = Device::factory()->for($user)->create(['status' => 'active']);

        $this->withToken($device->agent_token)
            ->postJson('/api/agent/heartbeat', [
                'os_version' => '14',
                'manufacturer' => 'Google',
                'model' => 'Pixel 8 Pro',
                'phone_number' => '+15550001111',
            ])
            ->assertOk()
            ->assertJsonPath('status', 'ok');

        $this->assertDatabaseHas('devices', [
            'id' => $device->id,
            'os_version' => '14',
            'phone_number' => '+15550001111',
        ]);
        $this->assertNotNull($device->fresh()->last_seen_at);
    }

    public function test_ingest_stores_calls_messages_and_locations()
    {
        $user = User::factory()->create();
        $device = Device::factory()->for($user)->create(['status' => 'active']);

        $response = $this->withToken($device->agent_token)
            ->postJson('/api/agent/ingest', [
                'calls' => [
                    ['direction' => 'incoming', 'contact_name' => 'Mom', 'phone_number' => '+1555000001', 'duration_seconds' => 120, 'started_at' => now()->subHour()],
                ],
                'messages' => [
                    ['platform' => 'whatsapp', 'direction' => 'incoming', 'contact_name' => 'Dad', 'phone_number' => '+1555000002', 'body' => 'Hello son', 'was_deleted' => false, 'sent_at' => now()->subMinutes(30)],
                ],
                'locations' => [
                    ['latitude' => 51.5074, 'longitude' => -0.1278, 'accuracy_meters' => 12.5, 'label' => 'Home', 'recorded_at' => now()->subMinutes(10)],
                ],
            ])
            ->assertOk()
            ->assertJson([
                'status' => 'ok',
                'ingested' => ['calls' => 1, 'messages' => 1, 'locations' => 1],
            ]);

        $this->assertDatabaseCount('device_calls', 1);
        $this->assertDatabaseCount('device_messages', 1);
        $this->assertDatabaseCount('device_locations', 1);

        $call = DeviceCall::first();
        $this->assertEquals($device->id, $call->device_id);
        $this->assertEquals('Mom', $call->contact_name);
    }

    public function test_ingest_validates_payload()
    {
        $user = User::factory()->create();
        $device = Device::factory()->for($user)->create(['status' => 'active']);

        $this->withToken($device->agent_token)
            ->postJson('/api/agent/ingest', [
                'locations' => [['latitude' => 91, 'longitude' => 0, 'recorded_at' => now()]],
            ])
            ->assertJsonValidationErrors('locations.0.latitude');

        $this->assertDatabaseCount('device_locations', 0);
    }

    public function test_ingest_without_known_keyword_does_not_create_alert()
    {
        $user = User::factory()->create();
        $device = Device::factory()->for($user)->create(['status' => 'active']);

        $this->withToken($device->agent_token)
            ->postJson('/api/agent/ingest', [
                'messages' => [
                    ['platform' => 'sms', 'direction' => 'incoming', 'body' => 'See you at dinner', 'sent_at' => now()],
                ],
            ])
            ->assertOk();

        $this->assertDatabaseCount('alerts', 0);
    }
}
