<?php

namespace Tests\Feature;

use App\Services\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_render()
    {
        $this->get('/about')->assertOk()->assertSee('About ROYALTRICO');
        $this->get('/privacy')->assertOk()->assertSee('Privacy policy');
        $this->get('/terms')->assertOk()->assertSee('Terms of service');
    }

    public function test_home_footer_links_to_pages()
    {
        $this->get('/')
            ->assertOk()
            ->assertSee(route('pages.about'))
            ->assertSee(route('pages.privacy'))
            ->assertSee(route('pages.terms'));
    }

    public function test_home_shows_the_configured_support_email()
    {
        Settings::set('support_email', 'help@royaltrico.test');

        $this->get('/')
            ->assertOk()
            ->assertSee('help@royaltrico.test')
            ->assertDontSee('support@yoursite.example');
    }
}
