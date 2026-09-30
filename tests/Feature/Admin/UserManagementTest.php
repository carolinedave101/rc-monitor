<?php

namespace Tests\Feature\Admin;

use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_view_accounts()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin/users')->assertForbidden();
        $this->actingAs($user)->get("/admin/users/{$user->id}")->assertForbidden();
    }

    public function test_admin_sees_account_list_with_counts()
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create(['name' => 'Jane Customer']);
        Device::factory()->for($customer)->count(2)->create();

        $this->actingAs($admin)
            ->get('/admin/users')
            ->assertOk()
            ->assertSee('Jane Customer')
            ->assertSee($customer->email);
    }

    public function test_admin_sees_account_detail_with_devices()
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();
        Device::factory()->for($customer)->create(['name' => 'Ava Phone']);

        $this->actingAs($admin)
            ->get("/admin/users/{$customer->id}")
            ->assertOk()
            ->assertSee('Ava Phone')
            ->assertSee($customer->email);
    }
}
