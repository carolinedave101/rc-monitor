<?php

namespace Tests\Feature;

use App\Models\Device;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlansTest extends TestCase
{
    use RefreshDatabase;

    protected function devicePayload(): array
    {
        return [
            'name' => 'Extra Phone',
            'os' => 'android',
            'consent_recorded' => '1',
        ];
    }

    public function test_default_limit_blocks_enrolling_more_than_two_devices()
    {
        $user = User::factory()->create();
        Device::factory()->for($user)->count(2)->create();

        $this->actingAs($user)
            ->post('/devices', $this->devicePayload())
            ->assertSessionHasErrors('name');

        $this->assertSame(2, $user->devices()->count());
    }

    public function test_standard_plan_allows_more_devices()
    {
        $plan = Plan::factory()->create(['device_limit' => 5]);
        $user = User::factory()->create(['plan_id' => $plan->id]);
        Device::factory()->for($user)->count(2)->create();

        $this->actingAs($user)
            ->post('/devices', $this->devicePayload())
            ->assertRedirect();

        $this->assertSame(3, $user->devices()->count());
    }

    public function test_admin_can_assign_a_plan_and_it_is_audited()
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();
        $plan = Plan::factory()->create(['device_limit' => 5]);

        $this->actingAs($admin)
            ->post("/admin/users/{$customer->id}/plan", ['plan_id' => $plan->id])
            ->assertRedirect();

        $customer->refresh();
        $this->assertSame($plan->id, $customer->plan_id);
        $this->assertNotNull($customer->plan_activated_at);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'user.plan.updated',
            'auditable_id' => $customer->id,
        ]);
    }

    public function test_billing_page_shows_plan_and_usage()
    {
        $plan = Plan::factory()->create(['name' => 'Standard', 'device_limit' => 5, 'price_cents' => 4900]);
        $user = User::factory()->create(['plan_id' => $plan->id]);
        Device::factory()->for($user)->create();

        $this->actingAs($user)
            ->get('/billing')
            ->assertOk()
            ->assertSee('Standard')
            ->assertSee('$49.00')
            ->assertSee('1 of 5 devices');
    }
}
