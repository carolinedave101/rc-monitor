<?php

namespace Tests\Feature;

use App\Models\Alert;
use App\Models\AlertRule;
use App\Models\Device;
use App\Models\DeviceMessage;
use App\Models\ServiceStep;
use App\Models\User;
use App\Notifications\AlertRaised;
use App\Services\AlertEngine;
use App\Services\JourneyManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_keyword_alert_notifies_the_rule_owner()
    {
        $user = User::factory()->create();
        $device = Device::factory()->for($user)->create();
        AlertRule::factory()->for($user)->keyword('help')->create();

        $message = DeviceMessage::factory()->for($device)->create(['body' => 'please help me']);
        app(AlertEngine::class)->evaluateMessage($device, $message);

        $this->assertDatabaseCount('alerts', 1);
        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'type' => AlertRaised::class,
        ]);
        $this->assertSame(1, $user->unreadNotifications()->count());
    }

    public function test_pausing_and_resuming_a_journey_notifies_the_customer()
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();
        $step = ServiceStep::factory()->for($customer)->create(['status' => 'in_progress']);

        $this->actingAs($admin);
        $manager = app(JourneyManager::class);

        $manager->pause($step, 'Awaiting payment', false);
        $this->assertSame(1, $customer->unreadNotifications()->count());

        $manager->resume($step->fresh());
        $this->assertSame(2, $customer->unreadNotifications()->count());

        $resumed = $customer->notifications()->get()->first(fn ($notification) => $notification->data['change'] === 'resumed');
        $this->assertNotNull($resumed);
        $this->assertSame('journey', $resumed->data['type']);
    }

    public function test_notifications_page_lists_and_marks_read()
    {
        $user = User::factory()->create();
        $device = Device::factory()->for($user)->create();
        $alert = Alert::factory()->for($user)->for($device)->create(['title' => 'Keyword match: "help"']);
        $user->notify(new AlertRaised($alert));

        $this->actingAs($user)
            ->get('/notifications')
            ->assertOk()
            ->assertSee('Keyword match: "help"')
            ->assertSee('New');

        $notification = $user->notifications()->first();

        $this->actingAs($user)
            ->post("/notifications/{$notification->id}/read")
            ->assertRedirect();

        $this->assertSame(0, $user->fresh()->unreadNotifications()->count());
    }

    public function test_mark_all_read()
    {
        $user = User::factory()->create();
        $device = Device::factory()->for($user)->create();
        $user->notify(new AlertRaised(Alert::factory()->for($user)->for($device)->create()));
        $user->notify(new AlertRaised(Alert::factory()->for($user)->for($device)->create()));

        $this->assertSame(2, $user->unreadNotifications()->count());

        $this->actingAs($user)
            ->post('/notifications/read-all')
            ->assertRedirect();

        $this->assertSame(0, $user->fresh()->unreadNotifications()->count());
    }
}
