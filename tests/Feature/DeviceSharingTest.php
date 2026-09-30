<?php

namespace Tests\Feature;

use App\Models\Device;
use App\Models\DeviceShare;
use App\Models\User;
use App\Notifications\ShareAccepted;
use App\Notifications\ShareInvited;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeviceSharingTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_invite_a_viewer_by_email()
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $device = Device::factory()->for($owner)->create();

        $this->actingAs($owner)
            ->post("/devices/{$device->id}/shares", ['email' => $viewer->email])
            ->assertRedirect();

        $this->assertDatabaseHas('device_shares', [
            'device_id' => $device->id,
            'owner_id' => $owner->id,
            'viewer_id' => $viewer->id,
            'email' => $viewer->email,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $viewer->id,
            'type' => ShareInvited::class,
        ]);

        $this->assertDatabaseHas('audit_logs', ['action' => 'share.invited']);
    }

    public function test_invite_for_an_unknown_email_is_unbound_until_signup()
    {
        $owner = User::factory()->create();
        $device = Device::factory()->for($owner)->create();

        $this->actingAs($owner)
            ->post("/devices/{$device->id}/shares", ['email' => 'future-partner@example.com'])
            ->assertRedirect();

        $this->assertDatabaseHas('device_shares', [
            'email' => 'future-partner@example.com',
            'viewer_id' => null,
            'status' => 'pending',
        ]);
    }

    public function test_invitee_can_accept_and_gains_read_access_with_consent_recorded()
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $device = Device::factory()->for($owner)->create();
        $share = DeviceShare::factory()->for($device)->create([
            'owner_id' => $owner->id,
            'viewer_id' => $viewer->id,
            'email' => $viewer->email,
            'status' => 'pending',
        ]);

        $this->actingAs($viewer)
            ->post("/shares/{$share->id}/accept")
            ->assertRedirect(route('shares.index'));

        $this->assertSame('accepted', $share->fresh()->status);
        $this->assertNotNull($share->fresh()->accepted_at);

        $this->assertDatabaseHas('consents', [
            'user_id' => $viewer->id,
            'device_id' => $device->id,
            'device_share_id' => $share->id,
            'type' => 'sharing',
        ]);

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $owner->id,
            'type' => ShareAccepted::class,
        ]);

        $this->actingAs($viewer)->get("/devices/{$device->id}")->assertOk();
        $this->actingAs($viewer)->get('/devices')->assertSee($device->name);
    }

    public function test_viewer_cannot_manage_the_shared_device()
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $device = Device::factory()->for($owner)->create();
        DeviceShare::factory()->for($device)->accepted()->create([
            'owner_id' => $owner->id,
            'viewer_id' => $viewer->id,
            'email' => $viewer->email,
        ]);

        $this->actingAs($viewer)
            ->patch("/devices/{$device->id}/status", ['status' => 'suspended'])
            ->assertForbidden();

        $this->actingAs($viewer)
            ->delete("/devices/{$device->id}")
            ->assertForbidden();

        $this->assertSame('active', $device->fresh()->status);
    }

    public function test_non_invitee_cannot_accept()
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $stranger = User::factory()->create();
        $device = Device::factory()->for($owner)->create();
        $share = DeviceShare::factory()->for($device)->create([
            'owner_id' => $owner->id,
            'viewer_id' => $viewer->id,
            'email' => $viewer->email,
            'status' => 'pending',
        ]);

        $this->actingAs($stranger)
            ->post("/shares/{$share->id}/accept")
            ->assertForbidden();

        $this->assertSame('pending', $share->fresh()->status);
    }

    public function test_unbound_invite_is_matched_by_email_on_accept()
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create(['email' => 'partner@example.com']);
        $device = Device::factory()->for($owner)->create();
        $share = DeviceShare::factory()->for($device)->create([
            'owner_id' => $owner->id,
            'viewer_id' => null,
            'email' => 'partner@example.com',
            'status' => 'pending',
        ]);

        $this->actingAs($viewer)
            ->post("/shares/{$share->id}/accept")
            ->assertRedirect();

        $this->assertSame($viewer->id, $share->fresh()->viewer_id);
        $this->assertSame('accepted', $share->fresh()->status);
    }

    public function test_owner_revoking_removes_the_viewers_access()
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $device = Device::factory()->for($owner)->create();
        $share = DeviceShare::factory()->for($device)->accepted()->create([
            'owner_id' => $owner->id,
            'viewer_id' => $viewer->id,
            'email' => $viewer->email,
        ]);

        $this->actingAs($viewer)->get("/devices/{$device->id}")->assertOk();

        $this->actingAs($owner)
            ->post("/shares/{$share->id}/revoke")
            ->assertRedirect();

        $this->assertSame('revoked', $share->fresh()->status);
        $this->assertNotNull($share->fresh()->revoked_at);

        $this->actingAs($viewer)->get("/devices/{$device->id}")->assertForbidden();
        $this->assertDatabaseHas('audit_logs', ['action' => 'share.revoked']);
    }

    public function test_viewer_can_revoke_access_themselves()
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $device = Device::factory()->for($owner)->create();
        $share = DeviceShare::factory()->for($device)->accepted()->create([
            'owner_id' => $owner->id,
            'viewer_id' => $viewer->id,
            'email' => $viewer->email,
        ]);

        $this->actingAs($viewer)
            ->post("/shares/{$share->id}/revoke")
            ->assertRedirect();

        $this->assertSame('revoked', $share->fresh()->status);
    }

    public function test_duplicate_invites_and_self_invites_are_rejected()
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $device = Device::factory()->for($owner)->create();

        $this->actingAs($owner)
            ->post("/devices/{$device->id}/shares", ['email' => $viewer->email])
            ->assertRedirect();

        $this->actingAs($owner)
            ->post("/devices/{$device->id}/shares", ['email' => $viewer->email])
            ->assertSessionHasErrors('email');

        $this->actingAs($owner)
            ->post("/devices/{$device->id}/shares", ['email' => $owner->email])
            ->assertSessionHasErrors('email');

        $this->assertSame(1, DeviceShare::count());
    }

    public function test_non_owner_cannot_invite_viewers()
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $device = Device::factory()->for($owner)->create();

        $this->actingAs($other)
            ->post("/devices/{$device->id}/shares", ['email' => 'someone@example.com'])
            ->assertForbidden();

        $this->assertSame(0, DeviceShare::count());
    }
}
