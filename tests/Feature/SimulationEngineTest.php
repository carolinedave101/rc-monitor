<?php

namespace Tests\Feature;

use App\Models\Device;
use App\Models\DeviceCall;
use App\Models\SimulationProfile;
use App\Models\User;
use App\Services\Settings;
use App\Services\SimulationEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SimulationEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_tick_generates_simulated_activity_and_updates_last_tick()
    {
        $device = Device::factory()->for(User::factory())->create();
        SimulationProfile::factory()->for($device)->create([
            'enabled' => true,
            'activity_level' => 'normal',
            'last_tick_at' => now()->subHour(),
        ]);

        $counts = app(SimulationEngine::class)->tickDevice($device->fresh());

        $this->assertGreaterThan(0, $counts['messages']);
        $this->assertNotNull($device->fresh()->simulationProfile->last_tick_at);
        $this->assertSame('simulated', $device->messages()->first()->source);
        $this->assertGreaterThan(0, $device->contacts()->count());
    }

    public function test_tick_does_nothing_without_an_enabled_profile()
    {
        $device = Device::factory()->for(User::factory())->create();
        SimulationProfile::factory()->for($device)->disabled()->create();

        $counts = app(SimulationEngine::class)->tickDevice($device->fresh());

        $this->assertSame([], $counts);
        $this->assertSame(0, DeviceCall::count());
    }

    public function test_backfill_populates_history_across_domains()
    {
        $device = Device::factory()->for(User::factory())->create();
        SimulationProfile::factory()->for($device)->create();

        $counts = app(SimulationEngine::class)->backfill($device->fresh(), 3);

        foreach (['calls', 'messages', 'locations', 'apps', 'browser', 'emails', 'media', 'diagnostics'] as $domain) {
            $this->assertGreaterThan(0, $counts[$domain], "Domain {$domain} should have simulated records.");
        }

        $this->assertGreaterThan(0, $device->messages()->where('source', 'simulated')->count());
        $this->assertSame(0, $device->messages()->where('source', 'agent')->count());
    }

    public function test_wipe_removes_only_simulated_records()
    {
        $device = Device::factory()->for(User::factory())->create();

        DeviceCall::create([
            'device_id' => $device->id,
            'direction' => 'incoming',
            'started_at' => now()->subDay(),
            'source' => 'agent',
        ]);

        DeviceCall::create([
            'device_id' => $device->id,
            'direction' => 'outgoing',
            'started_at' => now()->subDay(),
            'source' => 'simulated',
        ]);

        $deleted = app(SimulationEngine::class)->wipe($device);

        $this->assertSame(1, $deleted['calls']);
        $this->assertSame(1, DeviceCall::count());
        $this->assertSame('agent', DeviceCall::first()->source);
    }

    public function test_command_ticks_devices_when_simulation_is_enabled()
    {
        Settings::set('simulation_enabled', '1');

        $device = Device::factory()->for(User::factory())->create();
        SimulationProfile::factory()->for($device)->create([
            'last_tick_at' => now()->subHours(2),
        ]);

        $this->artisan('simulate:devices')->assertSuccessful();

        $this->assertGreaterThan(0, $device->messages()->count());
    }

    public function test_command_does_nothing_when_simulation_is_paused()
    {
        Settings::set('simulation_enabled', '0');

        $device = Device::factory()->for(User::factory())->create();
        SimulationProfile::factory()->for($device)->create([
            'last_tick_at' => now()->subHours(2),
        ]);

        $this->artisan('simulate:devices')->assertSuccessful();

        $this->assertSame(0, $device->messages()->count());
    }
}
