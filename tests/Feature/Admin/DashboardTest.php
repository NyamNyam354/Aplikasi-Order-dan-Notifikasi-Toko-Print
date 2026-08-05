<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_dashboard(): void
    {
        $user = User::factory()->admin()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Dashboard');
    }

    public function test_guest_cannot_view_admin_dashboard(): void
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_customer_cannot_view_admin_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $this->actingAs($user);

        $response = $this->get(route('admin.dashboard'));
        $response->assertStatus(403);
    }

    public function test_admin_login_page_is_displayed(): void
    {
        $response = $this->get(route('admin.login'));
        $response->assertStatus(200);
    }

    public function test_admin_can_login(): void
    {
        $user = User::factory()->admin()->create([
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);

        $response = $this->post(route('admin.login'), [
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_customer_cannot_login_as_admin(): void
    {
        $user = User::factory()->create([
            'email' => 'customer@test.com',
            'password' => 'password',
            'role' => 'customer',
        ]);

        $response = $this->post(route('admin.login'), [
            'email' => 'customer@test.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
