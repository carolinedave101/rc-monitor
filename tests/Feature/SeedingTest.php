<?php

namespace Tests\Feature;

use App\Models\Alert;
use App\Models\Device;
use App\Models\DeviceCall;
use App\Models\DeviceLocation;
use App\Models\DeviceMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeedingTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_populates_demo_data()
    {
        $this->seed();

        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
        $this->assertDatabaseHas('users', ['email' => 'partner@example.com']);
        $this->assertDatabaseCount('devices', 3);
        $this->assertGreaterThan(0, DeviceCall::count());
        $this->assertGreaterThan(0, DeviceMessage::count());
        $this->assertGreaterThan(0, DeviceLocation::count());
        $this->assertDatabaseCount('alert_rules', 2);
        $this->assertGreaterThan(0, Alert::count());
        $this->assertDatabaseCount('device_shares', 1);
        $this->assertDatabaseCount('consents', 1);
        $this->assertDatabaseCount('device_commands', 1);
        $this->assertDatabaseCount('service_steps', 5);
        $this->assertSame('active', Device::where('name', "Ava's Phone")->value('status'));
        $this->assertSame('pending', Device::where('name', 'Work Tablet')->value('status'));
        $this->assertSame('suspended', Device::where('name', "Mom's iPhone")->value('status'));
    }
}
