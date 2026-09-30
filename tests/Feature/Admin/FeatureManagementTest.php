<?php

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Models\Feature;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeatureManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_manage_features()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin/features')->assertForbidden();
    }

    public function test_admin_sees_feature_registry()
    {
        $admin = User::factory()->admin()->create();
        Feature::factory()->create(['name' => 'Call log', 'status' => 'live']);

        $this->actingAs($admin)
            ->get('/admin/features')
            ->assertOk()
            ->assertSee('Feature registry')
            ->assertSee('Call log');
    }

    public function test_admin_can_change_feature_status_and_it_is_audited()
    {
        $admin = User::factory()->admin()->create();
        $feature = Feature::factory()->create(['status' => 'coming_soon']);

        $this->actingAs($admin)
            ->patch("/admin/features/{$feature->id}", [
                'status' => 'simulated',
                'is_public' => '1',
            ])
            ->assertRedirect();

        $this->assertSame('simulated', $feature->fresh()->status);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'feature.status.updated',
            'auditable_type' => Feature::class,
            'auditable_id' => $feature->id,
        ]);

        $this->assertSame(
            ['from' => 'coming_soon', 'to' => 'simulated'],
            AuditLog::latest('id')->first()->meta,
        );
    }

    public function test_feature_status_must_be_valid()
    {
        $admin = User::factory()->admin()->create();
        $feature = Feature::factory()->create(['status' => 'live']);

        $this->actingAs($admin)
            ->patch("/admin/features/{$feature->id}", ['status' => 'bogus'])
            ->assertSessionHasErrors('status');

        $this->assertSame('live', $feature->fresh()->status);
    }

    public function test_feature_can_be_hidden_from_customers()
    {
        $admin = User::factory()->admin()->create();
        $feature = Feature::factory()->create(['status' => 'live', 'is_public' => true]);

        $this->actingAs($admin)
            ->patch("/admin/features/{$feature->id}", ['status' => 'live'])
            ->assertRedirect();

        $this->assertFalse($feature->fresh()->is_public);
    }
}
