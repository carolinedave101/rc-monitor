<?php

namespace Tests\Feature;

use App\Models\ServiceStep;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JourneyVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_service_plan_progress()
    {
        $user = User::factory()->create();
        ServiceStep::factory()->for($user)->completed()->create(['title' => 'Account setup', 'position' => 1]);
        ServiceStep::factory()->for($user)->inProgress()->create(['title' => 'Device enrollment', 'position' => 2]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Your service plan')
            ->assertSee('1 of 2 steps complete')
            ->assertSee('Device enrollment');
    }

    public function test_paused_plan_reason_is_visible_to_customer()
    {
        $user = User::factory()->create();
        ServiceStep::factory()->for($user)->create([
            'title' => 'Baseline review',
            'position' => 1,
            'status' => 'in_progress',
            'paused_at' => now(),
            'paused_reason' => 'Awaiting payment for the next phase',
        ]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Your plan is paused')
            ->assertSee('Awaiting payment for the next phase');
    }

    public function test_journey_page_lists_every_step()
    {
        $user = User::factory()->create();
        ServiceStep::factory()->for($user)->completed()->create(['title' => 'Account setup', 'position' => 1]);
        ServiceStep::factory()->for($user)->create(['title' => 'Alerts configured', 'position' => 2]);

        $this->actingAs($user)
            ->get('/journey')
            ->assertOk()
            ->assertSee('Your service plan')
            ->assertSee('Account setup')
            ->assertSee('Alerts configured')
            ->assertSee('Completed');
    }

    public function test_journey_page_handles_no_steps()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/journey')
            ->assertOk()
            ->assertSee('Your plan is being prepared');
    }
}
