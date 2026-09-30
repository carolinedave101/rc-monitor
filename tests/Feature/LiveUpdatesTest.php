<?php

namespace Tests\Feature;

use App\Models\Alert;
use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LiveUpdatesTest extends TestCase
{
    use RefreshDatabase;

    public function test_live_dashboard_requires_auth()
    {
        $this->get('/dashboard/live')->assertRedirect('/login');
    }

    public function test_live_dashboard_returns_stats_devices_and_alerts()
    {
        $user = User::factory()->create();
        $device = Device::factory()->for($user)->create(['status' => 'active', 'last_seen_at' => now()]);
        Alert::factory()->for($user)->for($device)->create();

        $this->actingAs($user)
            ->getJson('/dashboard/live')
            ->assertOk()
            ->assertJsonPath('stats.total', 1)
            ->assertJsonPath('stats.active', 1)
            ->assertJsonPath('stats.online', 1)
            ->assertJsonPath('stats.unread', 1)
            ->assertJsonPath('devices.0.id', $device->id)
            ->assertJsonPath('devices.0.online', true)
            ->assertJsonCount(1, 'alerts');
    }

    public function test_live_dashboard_is_scoped_to_the_current_user()
    {
        $user = User::factory()->create();
        Device::factory()->for(User::factory())->create();

        $this->actingAs($user)
            ->getJson('/dashboard/live')
            ->assertOk()
            ->assertJsonPath('stats.total', 0)
            ->assertJsonCount(0, 'devices');
    }

    public function test_live_device_endpoint_requires_ownership()
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $device = Device::factory()->for($other)->create();

        $this->actingAs($user)
            ->getJson("/devices/{$device->id}/live")
            ->assertForbidden();

        $this->actingAs($other)
            ->getJson("/devices/{$device->id}/live")
            ->assertOk()
            ->assertJsonPath('status', $device->status)
            ->assertJsonStructure(['status', 'online', 'last_seen_human']);
    }
}
