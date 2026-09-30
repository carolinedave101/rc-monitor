<?php

namespace Tests\Feature\Admin;

use App\Models\Device;
use App\Models\SimulationProfile;
use App\Models\User;
use App\Services\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SimulationManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_access_simulation()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin/simulation')->assertForbidden();
    }

    public function test_admin_sees_simulation_page()
    {
        $admin = User::factory()->admin()->create();
        Device::factory()->for(User::factory())->create(['name' => 'Ava Phone']);

        $this->actingAs($admin)
            ->get('/admin/simulation')
            ->assertOk()
            ->assertSee('Simulation engine')
            ->assertSee('Ava Phone');
    }

    public function test_admin_can_pause_simulation_globally_and_it_is_audited()
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->patch('/admin/simulation/settings')
            ->assertRedirect();

        $this->assertFalse(Settings::bool('simulation_enabled', true));
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'simulation.settings.updated',
        ]);
    }

    public function test_admin_can_enable_a_device_profile_and_it_is_audited()
    {
        $admin = User::factory()->admin()->create();
        $device = Device::factory()->for(User::factory())->create();

        $this->actingAs($admin)
            ->patch("/admin/simulation/devices/{$device->id}", [
                'enabled' => '1',
                'activity_level' => 'high',
            ])
            ->assertRedirect();

        $profile = SimulationProfile::first();
        $this->assertTrue($profile->enabled);
        $this->assertSame('high', $profile->activity_level);
        $this->assertSame($device->id, $profile->device_id);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'simulation.profile.updated',
            'auditable_id' => $profile->id,
        ]);
    }

    public function test_admin_can_backfill_and_it_is_audited()
    {
        $admin = User::factory()->admin()->create();
        $device = Device::factory()->for(User::factory())->create();
        SimulationProfile::factory()->for($device)->create();

        $this->actingAs($admin)
            ->post("/admin/simulation/devices/{$device->id}/backfill", ['days' => 2])
            ->assertRedirect();

        $this->assertGreaterThan(0, $device->messages()->count());
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'simulation.backfill',
            'auditable_id' => $device->id,
        ]);
    }

    public function test_admin_can_tick_and_wipe()
    {
        $admin = User::factory()->admin()->create();
        $device = Device::factory()->for(User::factory())->create();
        SimulationProfile::factory()->for($device)->create(['last_tick_at' => now()->subHours(2)]);

        $this->actingAs($admin)
            ->post("/admin/simulation/devices/{$device->id}/tick")
            ->assertRedirect();

        $this->assertGreaterThan(0, $device->messages()->count());

        $this->actingAs($admin)
            ->post("/admin/simulation/devices/{$device->id}/wipe")
            ->assertRedirect();

        $this->assertSame(0, $device->messages()->count());
        $this->assertDatabaseHas('audit_logs', ['action' => 'simulation.tick']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'simulation.wipe']);
    }

    public function test_backfill_days_are_validated()
    {
        $admin = User::factory()->admin()->create();
        $device = Device::factory()->for(User::factory())->create();

        $this->actingAs($admin)
            ->post("/admin/simulation/devices/{$device->id}/backfill", ['days' => 500])
            ->assertSessionHasErrors('days');
    }
}
