<?php

namespace Tests\Feature;

use App\Models\Alert;
use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UiTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_title_is_not_duplicated()
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<title>ROYALTRICO — Consent-Based Device &amp; Family Monitoring</title>', false);
    }

    public function test_status_dot_styles_are_defined()
    {
        $user = User::factory()->create();
        $device = Device::factory()->for($user)->create(['last_seen_at' => now()]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('.status-dot.online', false)
            ->assertSee($device->name);
    }

    public function test_alerts_pagination_uses_bootstrap_markup()
    {
        $user = User::factory()->create();

        foreach (range(1, 25) as $index) {
            Alert::create([
                'user_id' => $user->id,
                'type' => 'keyword',
                'severity' => 'warning',
                'title' => "Alert {$index}",
                'body' => 'Test alert body',
            ]);
        }

        $this->actingAs($user)
            ->get('/alerts')
            ->assertOk()
            ->assertSee('class="pagination"', false);
    }
}
