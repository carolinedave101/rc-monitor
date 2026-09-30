<?php

namespace Tests\Feature;

use App\Models\Feature;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_public_features_with_status_labels()
    {
        $user = User::factory()->create();

        Feature::factory()->create(['name' => 'Call log', 'status' => 'live', 'is_public' => true, 'sort' => 1]);
        Feature::factory()->create(['name' => 'Photo log', 'status' => 'coming_soon', 'is_public' => true, 'sort' => 2]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Service status')
            ->assertSee('Call log · Active')
            ->assertSee('Photo log · Coming soon');
    }

    public function test_dashboard_hides_non_public_and_disabled_features()
    {
        $user = User::factory()->create();

        Feature::factory()->create(['name' => 'Internal audit trail', 'status' => 'live', 'is_public' => false]);
        Feature::factory()->create(['name' => 'Retired feature', 'status' => 'disabled', 'is_public' => true]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertDontSee('Internal audit trail')
            ->assertDontSee('Retired feature');
    }
}
