<?php

namespace Tests\Feature;

use App\Models\Device;
use App\Models\SimulationProfile;
use App\Models\User;
use App\Services\SimulationEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeviceTabsTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_device_tab_renders(): void
    {
        $user = User::factory()->create();
        $device = Device::factory()->for($user)->create(['status' => 'active']);

        SimulationProfile::factory()->for($device)->create(['enabled' => true]);

        app(SimulationEngine::class)->backfill($device->fresh(), 2);

        $tabs = [
            'overview', 'calls', 'messages', 'locations', 'alerts', 'apps',
            'contacts', 'diagnostics', 'browser', 'emails', 'media', 'notes', 'calendar',
        ];

        foreach ($tabs as $tab) {
            $this->actingAs($user)
                ->get(route('devices.show', ['device' => $device, 'tab' => $tab]))
                ->assertOk();
        }
    }
}
