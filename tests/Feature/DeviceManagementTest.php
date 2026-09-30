<?php

namespace Tests\Feature;

use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeviceManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login()
    {
        $this->get('/devices')->assertRedirect('/login');
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/alerts')->assertRedirect('/login');
    }

    public function test_user_can_enroll_a_device()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/devices', [
                'name' => 'Ava\'s Phone',
                'manufacturer' => 'Google',
                'model' => 'Pixel 8',
                'os' => 'android',
                'phone_number' => '+15550000001',
                'consent_recorded' => '1',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('devices', [
            'name' => 'Ava\'s Phone',
            'user_id' => $user->id,
            'status' => 'pending',
            'consent_recorded' => true,
        ]);

        $device = Device::first();
        $this->assertEquals(64, strlen($device->agent_token));
        $this->assertTrue($device->agent_token !== Device::generateToken());
    }

    public function test_device_enrollment_requires_consent()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/devices', [
                'name' => 'No Consent Phone',
                'os' => 'android',
            ])
            ->assertSessionHasErrors('consent_recorded');

        $this->assertDatabaseCount('devices', 0);
    }

    public function test_user_can_view_only_own_devices()
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $own = Device::factory()->for($user)->create(['name' => 'Mine']);
        $theirs = Device::factory()->for($other)->create(['name' => 'Theirs']);

        $this->actingAs($user)
            ->get('/devices')
            ->assertSee('Mine')
            ->assertDontSee('Theirs');

        $this->actingAs($user)
            ->get('/devices/'.$theirs->id)
            ->assertForbidden();
    }

    public function test_user_can_record_consent_and_activate_device()
    {
        $user = User::factory()->create();
        $device = Device::factory()->for($user)->create(['status' => 'pending', 'consent_recorded' => false]);

        $this->actingAs($user)
            ->post("/devices/{$device->id}/consent")
            ->assertRedirect("/devices/{$device->id}");

        $this->assertDatabaseHas('devices', [
            'id' => $device->id,
            'status' => 'active',
            'consent_recorded' => true,
        ]);
        $this->assertNotNull($device->fresh()->consented_at);
    }

    public function test_user_can_suspend_and_reactivate_device()
    {
        $user = User::factory()->create();
        $device = Device::factory()->for($user)->create(['status' => 'active']);

        $this->actingAs($user)
            ->patch("/devices/{$device->id}/status", ['status' => 'suspended'])
            ->assertRedirect("/devices/{$device->id}");

        $this->assertEquals('suspended', $device->fresh()->status);

        $this->actingAs($user)
            ->patch("/devices/{$device->id}/status", ['status' => 'active'])
            ->assertRedirect("/devices/{$device->id}");

        $this->assertEquals('active', $device->fresh()->status);
    }

    public function test_user_can_remove_a_device()
    {
        $user = User::factory()->create();
        $device = Device::factory()->for($user)->create();

        $this->actingAs($user)
            ->delete("/devices/{$device->id}")
            ->assertRedirect('/devices');

        $this->assertDatabaseMissing('devices', ['id' => $device->id]);
    }

    public function test_several_tabs_render_on_device_show()
    {
        $user = User::factory()->create();
        $device = Device::factory()->for($user)->create();

        $tabs = [
            'overview', 'calls', 'messages', 'locations', 'alerts', 'apps',
            'contacts', 'diagnostics', 'browser', 'emails', 'media', 'notes', 'calendar',
        ];

        foreach ($tabs as $tab) {
            $this->actingAs($user)
                ->get("/devices/{$device->id}?tab={$tab}")
                ->assertOk()
                ->assertSee($device->name);
        }
    }

    public function test_unknown_tab_falls_back_to_overview()
    {
        $user = User::factory()->create();
        $device = Device::factory()->for($user)->create();

        $this->actingAs($user)
            ->get("/devices/{$device->id}?tab=bogus")
            ->assertOk()
            ->assertSee('Setup');
    }
}
