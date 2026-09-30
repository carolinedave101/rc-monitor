<?php

namespace Tests\Feature\Admin;

use App\Models\Device;
use App\Models\ServiceStep;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JourneyManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_manage_journey()
    {
        $user = User::factory()->create();
        $step = ServiceStep::factory()->for($user)->create();

        $this->actingAs($user)
            ->post("/admin/users/{$user->id}/steps", ['title' => 'Sneaky'])
            ->assertForbidden();

        $this->actingAs($user)
            ->post("/admin/steps/{$step->id}/start")
            ->assertForbidden();
    }

    public function test_admin_can_add_a_step_and_it_is_audited()
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();
        ServiceStep::factory()->for($customer)->create(['position' => 1]);

        $this->actingAs($admin)
            ->post("/admin/users/{$customer->id}/steps", [
                'title' => 'Review week one',
                'description' => 'Go through the first week of activity together.',
                'requires_payment' => '1',
            ])
            ->assertRedirect();

        $step = ServiceStep::latest('id')->first();
        $this->assertSame(2, $step->position);
        $this->assertTrue($step->requires_payment);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'journey.step.created',
            'auditable_id' => $step->id,
        ]);
    }

    public function test_starting_a_step_sets_it_in_progress()
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();
        $step = ServiceStep::factory()->for($customer)->create(['status' => 'pending']);

        $this->actingAs($admin)
            ->post("/admin/steps/{$step->id}/start")
            ->assertRedirect();

        $this->assertSame('in_progress', $step->fresh()->status);
    }

    public function test_completing_a_step_continues_the_journey()
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();
        $first = ServiceStep::factory()->for($customer)->create(['position' => 1, 'status' => 'in_progress']);
        $second = ServiceStep::factory()->for($customer)->create(['position' => 2, 'status' => 'pending']);

        $this->actingAs($admin)
            ->post("/admin/steps/{$first->id}/advance")
            ->assertRedirect();

        $first->refresh();
        $this->assertSame('completed', $first->status);
        $this->assertNotNull($first->completed_at);
        $this->assertSame('in_progress', $second->fresh()->status);
    }

    public function test_pausing_with_service_suspension_suspends_devices_and_resume_restores()
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();
        $device = Device::factory()->for($customer)->create(['status' => 'active']);
        $step = ServiceStep::factory()->for($customer)->create(['status' => 'in_progress']);

        $this->actingAs($admin)
            ->post("/admin/steps/{$step->id}/pause", [
                'reason' => 'Awaiting payment for the next phase',
                'suspend_services' => '1',
            ])
            ->assertRedirect();

        $step->refresh();
        $this->assertNotNull($step->paused_at);
        $this->assertSame('Awaiting payment for the next phase', $step->paused_reason);

        $this->assertSame('suspended', $device->fresh()->status);
        $this->assertTrue($device->fresh()->pause_suspended);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'journey.step.paused',
            'auditable_id' => $step->id,
        ]);

        $this->actingAs($admin)
            ->post("/admin/steps/{$step->id}/resume")
            ->assertRedirect();

        $this->assertNull($step->fresh()->paused_at);
        $this->assertSame('active', $device->fresh()->status);
        $this->assertFalse($device->fresh()->pause_suspended);
    }

    public function test_pausing_without_suspension_keeps_devices_active()
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();
        $device = Device::factory()->for($customer)->create(['status' => 'active']);
        $step = ServiceStep::factory()->for($customer)->create(['status' => 'in_progress']);

        $this->actingAs($admin)
            ->post("/admin/steps/{$step->id}/pause", ['reason' => 'Internal review'])
            ->assertRedirect();

        $this->assertSame('active', $device->fresh()->status);
        $this->assertFalse($device->fresh()->pause_suspended);
        $this->assertFalse($step->fresh()->pause_suspends_services);
    }

    public function test_pause_requires_a_reason()
    {
        $admin = User::factory()->admin()->create();
        $step = ServiceStep::factory()->for(User::factory())->create();

        $this->actingAs($admin)
            ->post("/admin/steps/{$step->id}/pause")
            ->assertSessionHasErrors('reason');

        $this->assertNull($step->fresh()->paused_at);
    }
}
