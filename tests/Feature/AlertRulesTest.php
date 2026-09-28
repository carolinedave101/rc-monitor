<?php

namespace Tests\Feature;

use App\Models\Alert;
use App\Models\AlertRule;
use App\Models\Device;
use App\Models\DeviceLocation;
use App\Models\DeviceMessage;
use App\Models\User;
use App\Services\AlertEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlertRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_keyword_rule()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/alerts/rules', [
                'type' => 'keyword',
                'keyword' => 'help',
                'severity' => 'critical',
            ])
            ->assertRedirect('/alerts/rules');

        $this->assertDatabaseHas('alert_rules', [
            'user_id' => $user->id,
            'type' => 'keyword',
            'keyword' => 'help',
            'severity' => 'critical',
            'enabled' => true,
        ]);
    }

    public function test_user_can_create_geofence_rule()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/alerts/rules', [
                'type' => 'geofence',
                'latitude' => 51.5074,
                'longitude' => -0.1278,
                'radius_meters' => 500,
                'severity' => 'warning',
            ])
            ->assertRedirect('/alerts/rules');

        $this->assertDatabaseHas('alert_rules', [
            'user_id' => $user->id,
            'type' => 'geofence',
        ]);
    }

    public function test_geofence_rule_requires_coordinates()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/alerts/rules', [
                'type' => 'geofence',
                'severity' => 'warning',
            ])
            ->assertSessionHasErrors(['latitude', 'longitude', 'radius_meters']);
    }

    public function test_rule_cannot_target_another_users_device()
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $theirs = Device::factory()->for($other)->create();

        $this->actingAs($user)
            ->post('/alerts/rules', [
                'type' => 'keyword',
                'keyword' => 'help',
                'device_id' => $theirs->id,
                'severity' => 'warning',
            ])
            ->assertForbidden();
    }

    public function test_user_can_toggle_and_delete_rules()
    {
        $user = User::factory()->create();
        $rule = AlertRule::factory()->for($user)->create();

        $this->actingAs($user)
            ->post("/alerts/rules/{$rule->id}/toggle")
            ->assertRedirect('/alerts/rules');

        $this->assertFalse($rule->fresh()->enabled);

        $this->actingAs($user)
            ->delete("/alerts/rules/{$rule->id}")
            ->assertRedirect('/alerts/rules');

        $this->assertDatabaseMissing('alert_rules', ['id' => $rule->id]);
    }

    public function test_keyword_rule_fires_alert_and_marks_read_on_visit()
    {
        $user = User::factory()->create();

        $rule = AlertRule::factory()->for($user)->create([
            'type' => 'keyword',
            'keyword' => 'urgent',
            'severity' => 'critical',
            'device_id' => null,
        ]);

        $device = Device::factory()->for($user)->create();

        app(AlertEngine::class)->evaluateMessage(
            $device,
            DeviceMessage::factory()->create([
                'device_id' => $device->id,
                'direction' => 'incoming',
                'body' => 'This is urgently important',
            ])
        );

        $alert = Alert::where('user_id', $user->id)->first();
        $this->assertNotNull($alert);
        $this->assertEquals('critical', $alert->severity);
        $this->assertEquals($device->id, $alert->device_id);

        $this->actingAs($user)->get('/alerts')->assertSee('urgent');

        $this->assertNotNull($alert->fresh()->read_at);
    }

    public function test_geofence_rule_fires_alert_and_self_disables()
    {
        $user = User::factory()->create();

        $rule = AlertRule::factory()->for($user)->create([
            'type' => 'geofence',
            'latitude' => 51.5074,
            'longitude' => -0.1278,
            'radius_meters' => 500,
        ]);

        $device = Device::factory()->for($user)->create();

        app(AlertEngine::class)->evaluateLocation(
            $device,
            DeviceLocation::factory()->create([
                'device_id' => $device->id,
                'latitude' => 51.2500,
                'longitude' => -0.1278,
            ])
        );

        $alert = Alert::where('user_id', $user->id)->where('type', 'geofence')->first();
        $this->assertNotNull($alert);
        $this->assertFalse($rule->fresh()->enabled);
    }

    public function test_geofence_rule_does_not_fire_when_inside_zone()
    {
        $user = User::factory()->create();

        $rule = AlertRule::factory()->for($user)->create([
            'type' => 'geofence',
            'latitude' => 51.5074,
            'longitude' => -0.1278,
            'radius_meters' => 500,
        ]);

        $device = Device::factory()->for($user)->create();

        app(AlertEngine::class)->evaluateLocation(
            $device,
            DeviceLocation::factory()->create([
                'device_id' => $device->id,
                'latitude' => 51.5074,
                'longitude' => -0.1278,
            ])
        );

        $this->assertDatabaseCount('alerts', 0);
        $this->assertTrue($rule->fresh()->enabled);
    }
}
