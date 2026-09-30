<?php

namespace Tests\Feature\Admin;

use App\Models\Device;
use App\Models\DeviceFeature;
use App\Models\Feature;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeviceFeatureManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_view_device_detail()
    {
        $user = User::factory()->create();
        $device = Device::factory()->for($user)->create();

        $this->actingAs($user)->get("/admin/devices/{$device->id}")->assertForbidden();
    }

    public function test_admin_sees_device_detail_with_feature_states()
    {
        $admin = User::factory()->admin()->create();
        $device = Device::factory()->for(User::factory())->create(['name' => 'Ava Phone']);
        Feature::factory()->create(['name' => 'Call log', 'code' => 'call_log']);

        $this->actingAs($admin)
            ->get("/admin/devices/{$device->id}")
            ->assertOk()
            ->assertSee('Ava Phone')
            ->assertSee('Call log')
            ->assertSee('Feature states on this device');
    }

    public function test_admin_can_toggle_a_device_feature_and_it_is_audited()
    {
        $admin = User::factory()->admin()->create();
        $device = Device::factory()->for(User::factory())->create();
        $feature = Feature::factory()->create(['code' => 'photo_log']);

        $this->actingAs($admin)
            ->patch("/admin/devices/{$device->id}/features/{$feature->id}", ['enabled' => '0'])
            ->assertRedirect();

        $state = DeviceFeature::first();
        $this->assertSame($device->id, $state->device_id);
        $this->assertSame($feature->id, $state->feature_id);
        $this->assertFalse($state->enabled);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'device.feature.updated',
            'auditable_id' => $state->id,
        ]);
    }

    public function test_toggling_again_updates_the_existing_state()
    {
        $admin = User::factory()->admin()->create();
        $device = Device::factory()->for(User::factory())->create();
        $feature = Feature::factory()->create();

        $this->actingAs($admin)->patch("/admin/devices/{$device->id}/features/{$feature->id}", ['enabled' => '0']);
        $this->actingAs($admin)->patch("/admin/devices/{$device->id}/features/{$feature->id}", ['enabled' => '1']);

        $this->assertSame(1, DeviceFeature::count());
        $this->assertTrue(DeviceFeature::first()->enabled);
    }
}
