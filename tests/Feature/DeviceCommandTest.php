<?php

namespace Tests\Feature;

use App\Models\Device;
use App\Models\DeviceCommand;
use App\Models\SimulationProfile;
use App\Models\User;
use App\Notifications\CommandCompleted;
use App\Services\SimulationEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeviceCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_queue_a_command()
    {
        $owner = User::factory()->create();
        $device = Device::factory()->for($owner)->create();

        $this->actingAs($owner)
            ->post("/devices/{$device->id}/commands", ['type' => 'lock'])
            ->assertRedirect();

        $this->assertDatabaseHas('device_commands', [
            'device_id' => $device->id,
            'requested_by' => $owner->id,
            'type' => 'lock',
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('audit_logs', ['action' => 'device.command.queued']);
    }

    public function test_non_owner_cannot_queue_a_command()
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $device = Device::factory()->for($owner)->create();

        $this->actingAs($other)
            ->post("/devices/{$device->id}/commands", ['type' => 'lock'])
            ->assertForbidden();

        $this->assertSame(0, DeviceCommand::count());
    }

    public function test_command_type_must_be_valid()
    {
        $owner = User::factory()->create();
        $device = Device::factory()->for($owner)->create();

        $this->actingAs($owner)
            ->post("/devices/{$device->id}/commands", ['type' => 'explode'])
            ->assertSessionHasErrors('type');
    }

    public function test_heartbeat_delivers_pending_commands_and_marks_them_sent()
    {
        $owner = User::factory()->create();
        $device = Device::factory()->for($owner)->create(['status' => 'active']);
        $command = DeviceCommand::factory()->for($device)->create(['type' => 'ring']);

        $this->withToken($device->agent_token)
            ->postJson('/api/agent/heartbeat')
            ->assertOk()
            ->assertJsonPath('commands.0.id', $command->id)
            ->assertJsonPath('commands.0.type', 'ring');

        $this->assertSame('sent', $command->fresh()->status);
        $this->assertNotNull($command->fresh()->sent_at);
    }

    public function test_agent_can_acknowledge_a_command_and_owner_is_notified()
    {
        $owner = User::factory()->create();
        $device = Device::factory()->for($owner)->create(['status' => 'active']);
        $command = DeviceCommand::factory()->for($device)->sent()->create();

        $this->withToken($device->agent_token)
            ->postJson('/api/agent/commands/ack', [
                'commands' => [
                    ['id' => $command->id, 'status' => 'acknowledged', 'result' => 'Device locked'],
                ],
            ])
            ->assertOk()
            ->assertJsonPath('updated.acknowledged', 1);

        $this->assertSame('acknowledged', $command->fresh()->status);
        $this->assertSame('Device locked', $command->fresh()->result);
        $this->assertNotNull($command->fresh()->acknowledged_at);

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $owner->id,
            'type' => CommandCompleted::class,
        ]);
    }

    public function test_agent_cannot_acknowledge_another_devices_command()
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $device = Device::factory()->for($owner)->create(['status' => 'active']);
        $otherDevice = Device::factory()->for($other)->create(['status' => 'active']);
        $command = DeviceCommand::factory()->for($otherDevice)->sent()->create();

        $this->withToken($device->agent_token)
            ->postJson('/api/agent/commands/ack', [
                'commands' => [
                    ['id' => $command->id, 'status' => 'acknowledged'],
                ],
            ])
            ->assertOk()
            ->assertJsonPath('updated.acknowledged', 0);

        $this->assertSame('sent', $command->fresh()->status);
    }

    public function test_simulated_tick_acknowledges_commands()
    {
        $owner = User::factory()->create();
        $device = Device::factory()->for($owner)->create();
        SimulationProfile::factory()->for($device)->create(['last_tick_at' => now()->subHour()]);
        $command = DeviceCommand::factory()->for($device)->sent()->create();

        app(SimulationEngine::class)->tickDevice($device->fresh());

        $this->assertSame('acknowledged', $command->fresh()->status);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $owner->id,
            'type' => CommandCompleted::class,
        ]);
    }
}
