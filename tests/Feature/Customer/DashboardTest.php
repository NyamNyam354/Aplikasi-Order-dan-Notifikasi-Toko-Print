<?php

namespace Tests\Feature\Customer;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_view_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $this->actingAs($user);

        $response = $this->get(route('customer.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Dashboard');
    }

    public function test_dashboard_shows_stats(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $this->actingAs($user);

        Order::factory()->count(3)->create(['user_id' => $user->id, 'status' => OrderStatus::PENDING]);
        Order::factory()->count(2)->create(['user_id' => $user->id, 'status' => OrderStatus::COMPLETED]);

        $response = $this->get(route('customer.dashboard'));
        $response->assertSee('5'); // total
        $response->assertSee('3'); // pending
        $response->assertSee('2'); // completed
    }

    public function test_guest_cannot_view_customer_dashboard(): void
    {
        $response = $this->get(route('customer.dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_admin_cannot_view_customer_dashboard(): void
    {
        $user = User::factory()->admin()->create();
        $this->actingAs($user);

        $response = $this->get(route('customer.dashboard'));
        $response->assertStatus(403);
    }
}
