<?php

namespace Tests\Feature\Admin;

use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeviceManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_manage_devices()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin/devices')->assertForbidden();
    }

    public function test_admin_sees_devices_from_every_account()
    {
        $admin = User::factory()->admin()->create();
        $other = User::factory()->create();
        Device::factory()->for($other)->create(['name' => 'Other Device']);

        $this->actingAs($admin)
            ->get('/admin/devices')
            ->assertOk()
            ->assertSee('Other Device');
    }

    public function test_admin_can_change_device_status_and_it_is_audited()
    {
        $admin = User::factory()->admin()->create();
        $device = Device::factory()->for(User::factory())->create(['status' => 'active']);

        $this->actingAs($admin)
            ->patch("/admin/devices/{$device->id}", ['status' => 'suspended'])
            ->assertRedirect();

        $this->assertSame('suspended', $device->fresh()->status);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'device.status.updated',
            'auditable_type' => Device::class,
            'auditable_id' => $device->id,
        ]);
    }

    public function test_device_status_must_be_valid()
    {
        $admin = User::factory()->admin()->create();
        $device = Device::factory()->for(User::factory())->create(['status' => 'active']);

        $this->actingAs($admin)
            ->patch("/admin/devices/{$device->id}", ['status' => 'hacked'])
            ->assertSessionHasErrors('status');

        $this->assertSame('active', $device->fresh()->status);
    }

    public function test_admin_can_rotate_agent_token_and_it_is_audited()
    {
        $admin = User::factory()->admin()->create();
        $device = Device::factory()->for(User::factory())->create();
        $original = $device->agent_token;

        $this->actingAs($admin)
            ->post("/admin/devices/{$device->id}/rotate-token")
            ->assertRedirect();

        $this->assertNotSame($original, $device->fresh()->agent_token);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'device.token.rotated',
            'auditable_id' => $device->id,
        ]);
    }
}
