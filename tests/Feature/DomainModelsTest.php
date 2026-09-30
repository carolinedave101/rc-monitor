<?php

namespace Tests\Feature;

use App\Models\Device;
use App\Models\DeviceAppActivity;
use App\Models\DeviceBrowserHistory;
use App\Models\DeviceCalendarEvent;
use App\Models\DeviceContact;
use App\Models\DeviceDiagnostic;
use App\Models\DeviceEmail;
use App\Models\DeviceMedia;
use App\Models\DeviceNote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DomainModelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_activity_domain_factory_persists_against_a_device()
    {
        $device = Device::factory()->for(User::factory())->create();

        DeviceAppActivity::factory()->for($device)->create();
        DeviceContact::factory()->for($device)->create();
        DeviceDiagnostic::factory()->for($device)->create();
        DeviceBrowserHistory::factory()->for($device)->create();
        DeviceEmail::factory()->for($device)->create();
        DeviceMedia::factory()->for($device)->create();
        DeviceNote::factory()->for($device)->create();
        DeviceCalendarEvent::factory()->for($device)->create();

        $this->assertSame(1, $device->appActivities()->count());
        $this->assertSame(1, $device->contacts()->count());
        $this->assertSame(1, $device->diagnostics()->count());
        $this->assertSame(1, $device->browserHistories()->count());
        $this->assertSame(1, $device->emails()->count());
        $this->assertSame(1, $device->media()->count());
        $this->assertSame(1, $device->notes()->count());
        $this->assertSame(1, $device->calendarEvents()->count());
    }

    public function test_domain_records_default_to_agent_source()
    {
        $device = Device::factory()->for(User::factory())->create();

        $app = $device->appActivities()->create([
            'app_name' => 'Chrome',
            'launched_at' => now(),
        ]);

        $this->assertSame('agent', $app->fresh()->source);
        $this->assertSame('device_app_activities', $app->getTable());
        $this->assertSame('device_media', (new DeviceMedia)->getTable());
        $this->assertSame('device_browser_histories', (new DeviceBrowserHistory)->getTable());
    }
}
