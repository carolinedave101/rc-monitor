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
        $this->assertSame('agent', $call->source);
    }

    public function test_ingest_cannot_spoof_record_source()
    {
        $user = User::factory()->create();
        $device = Device::factory()->for($user)->create(['status' => 'active']);

        $this->withToken($device->agent_token)
            ->postJson('/api/agent/ingest', [
                'calls' => [
                    ['direction' => 'incoming', 'started_at' => now()->subHour(), 'source' => 'simulated'],
                ],
            ])
            ->assertOk();

        $this->assertSame('agent', DeviceCall::first()->source);
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

    public function test_ingest_stores_extended_domains()
    {
        $user = User::factory()->create();
        $device = Device::factory()->for($user)->create(['status' => 'active']);

        $response = $this->withToken($device->agent_token)
            ->postJson('/api/agent/ingest', [
                'apps' => [
                    ['app_name' => 'WhatsApp', 'package' => 'com.whatsapp', 'category' => 'social', 'duration_seconds' => 300, 'launched_at' => now()->subMinutes(20)],
                ],
                'contacts' => [
                    ['name' => 'Mom', 'phone_number' => '+15550000001', 'email' => 'mom@example.com'],
                ],
                'browser' => [
                    ['url' => 'https://www.wikipedia.org', 'domain' => 'wikipedia.org', 'title' => 'Wikipedia', 'visited_at' => now()->subMinutes(15)],
                ],
                'emails' => [
                    ['direction' => 'incoming', 'address' => 'school@example.com', 'subject' => 'Newsletter', 'snippet' => 'This week at school', 'sent_at' => now()->subHours(2)],
                ],
                'media' => [
                    ['type' => 'photo', 'filename' => 'IMG_0001.jpg', 'size_mb' => 3.4, 'taken_at' => now()->subHours(3)],
                ],
                'notes' => [
                    ['title' => 'Homework', 'body' => 'Math page 12'],
                ],
                'calendar' => [
                    ['title' => 'School pickup', 'location' => 'School', 'starts_at' => now()->addDay(), 'ends_at' => now()->addDay()->addHour()],
                ],
                'diagnostics' => [
                    'battery_percent' => 72,
                    'is_charging' => false,
                    'storage_used_mb' => 64000,
                    'storage_total_mb' => 128000,
                    'network' => 'wifi',
                    'recorded_at' => now()->subMinutes(5),
                ],
            ])
            ->assertOk()
            ->assertJsonPath('ingested.apps', 1)
            ->assertJsonPath('ingested.contacts', 1)
            ->assertJsonPath('ingested.browser', 1)
            ->assertJsonPath('ingested.emails', 1)
            ->assertJsonPath('ingested.media', 1)
            ->assertJsonPath('ingested.notes', 1)
            ->assertJsonPath('ingested.calendar', 1)
            ->assertJsonPath('ingested.diagnostics', 1);

        $this->assertDatabaseCount('device_app_activities', 1);
        $this->assertDatabaseCount('device_contacts', 1);
        $this->assertDatabaseCount('device_browser_histories', 1);
        $this->assertDatabaseCount('device_emails', 1);
        $this->assertDatabaseCount('device_media', 1);
        $this->assertDatabaseCount('device_notes', 1);
        $this->assertDatabaseCount('device_calendar_events', 1);
        $this->assertDatabaseCount('device_diagnostics', 1);

        $this->assertDatabaseHas('device_app_activities', ['source' => 'agent']);
        $this->assertDatabaseHas('device_contacts', ['source' => 'agent']);
        $this->assertDatabaseHas('device_diagnostics', ['source' => 'agent', 'battery_percent' => 72]);
    }

    public function test_ingest_does_not_duplicate_contacts()
    {
        $user = User::factory()->create();
        $device = Device::factory()->for($user)->create(['status' => 'active']);

        $payload = [
            'contacts' => [
                ['name' => 'Mom', 'phone_number' => '+15550000001'],
            ],
        ];

        $this->withToken($device->agent_token)->postJson('/api/agent/ingest', $payload)->assertOk();
        $this->withToken($device->agent_token)->postJson('/api/agent/ingest', $payload)->assertOk();

        $this->assertDatabaseCount('device_contacts', 1);
    }

    public function test_ingest_validates_extended_domains()
    {
        $user = User::factory()->create();
        $device = Device::factory()->for($user)->create(['status' => 'active']);

        $this->withToken($device->agent_token)
            ->postJson('/api/agent/ingest', [
                'apps' => [['app_name' => 'Chrome']],
                'diagnostics' => ['battery_percent' => 150, 'recorded_at' => now()],
            ])
            ->assertJsonValidationErrors(['apps.0.launched_at', 'diagnostics.battery_percent']);

        $this->assertDatabaseCount('device_app_activities', 0);
        $this->assertDatabaseCount('device_diagnostics', 0);
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
