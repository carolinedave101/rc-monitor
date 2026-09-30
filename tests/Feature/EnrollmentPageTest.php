<?php

namespace Tests\Feature;

use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnrollmentPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_enrollment_page_renders_for_a_valid_token()
    {
        $device = Device::factory()->for(User::factory())->create(['name' => 'Ava Phone']);

        $this->get("/enroll/{$device->agent_token}")
            ->assertOk()
            ->assertSee('Set up Ava Phone')
            ->assertSee($device->agent_token)
            ->assertSee('Consent reminder');
    }

    public function test_enrollment_page_returns_404_for_unknown_token()
    {
        $this->get('/enroll/not-a-real-token')->assertNotFound();
    }

    public function test_enrollment_page_does_not_expose_the_owners_email()
    {
        $owner = User::factory()->create(['email' => 'private-owner@example.com']);
        $device = Device::factory()->for($owner)->create();

        $this->get("/enroll/{$device->agent_token}")
            ->assertOk()
            ->assertDontSee('private-owner@example.com');
    }

    public function test_device_page_shows_qr_and_enrollment_link()
    {
        $owner = User::factory()->create();
        $device = Device::factory()->for($owner)->create();

        $this->actingAs($owner)
            ->get("/devices/{$device->id}")
            ->assertOk()
            ->assertSee(route('enroll.show', $device->agent_token))
            ->assertSee('<svg', false);
    }
}
