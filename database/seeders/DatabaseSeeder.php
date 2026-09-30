<?php

namespace Database\Seeders;

use App\Models\Alert;
use App\Models\AlertRule;
use App\Models\Device;
use App\Models\DeviceCall;
use App\Models\DeviceLocation;
use App\Models\DeviceMessage;
use App\Models\User;
use App\Services\SimulationEngine;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(FeatureSeeder::class);

        $user = User::factory()->admin()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $active = Device::factory()->for($user)->create([
            'name' => "Ava's Phone",
            'manufacturer' => 'Google',
            'model' => 'Pixel 8',
            'os' => 'android',
            'status' => 'active',
            'last_seen_at' => now(),
        ]);

        $active->simulationProfile()->create([
            'enabled' => true,
            'activity_level' => 'normal',
            'last_tick_at' => now()->subMinutes(15),
        ]);

        app(SimulationEngine::class)->backfill($active, 14);

        Device::factory()->for($user)->create([
            'name' => 'Work Tablet',
            'manufacturer' => 'Samsung',
            'model' => 'Galaxy Tab S9',
            'os' => 'android',
            'status' => 'pending',
            'last_seen_at' => null,
        ]);

        $suspended = Device::factory()->for($user)->create([
            'name' => "Mom's iPhone",
            'manufacturer' => 'Apple',
            'model' => 'iPhone 15',
            'os' => 'ios',
            'status' => 'suspended',
            'last_seen_at' => now()->subDays(3),
        ]);

        DeviceCall::factory()->count(12)->for($active)->create();
        DeviceMessage::factory()->count(20)->for($active)->create();
        DeviceLocation::factory()->count(10)->for($active)->create();

        DeviceCall::factory()->count(3)->for($suspended)->create();

        AlertRule::factory()->for($user)->keyword('help')->create([
            'device_id' => $active->id,
            'severity' => 'critical',
        ]);

        AlertRule::factory()->for($user)->geofence()->create([
            'device_id' => null,
            'severity' => 'warning',
            'enabled' => false,
        ]);

        Alert::factory()->for($user)->for($active)->count(4)->create();
        Alert::factory()->for($user)->for($active)->read()->create();

        $steps = [
            ['Account & consent', 'Registration completed and consent recorded.', 'completed'],
            ['Device enrollment & agent connection', 'Install the agent and connect the first device.', 'in_progress'],
            ['Baseline activity review', 'Walk through the first days of calls, messages and locations together.', 'pending'],
            ['Alerts configured & tuned', 'Set keyword and geofence rules that fit the family.', 'pending'],
            ['Ongoing monitoring & support', 'Regular check-ins and support for the account.', 'pending'],
        ];

        foreach ($steps as $index => [$title, $description, $status]) {
            $user->serviceSteps()->create([
                'title' => $title,
                'description' => $description,
                'position' => $index + 1,
                'status' => $status,
                'completed_at' => $status === 'completed' ? now()->subDays(6) : null,
            ]);
        }
    }
}
