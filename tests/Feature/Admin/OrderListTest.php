<?php

namespace Tests\Feature\Admin;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderListTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_order_list(): void
    {
        $user = User::factory()->admin()->create();
        $this->actingAs($user);

        Order::factory()->count(5)->create();

        $response = $this->get(route('admin.orders.index'));
        $response->assertStatus(200);
    }

    public function test_admin_can_search_orders(): void
    {
        $user = User::factory()->admin()->create();
        $this->actingAs($user);

        Order::factory()->create(['order_number' => 'ORD-20260704-0001']);

        $response = $this->get(route('admin.orders.index', ['search' => 'ORD-20260704']));
        $response->assertStatus(200);
        $response->assertSee('ORD-20260704-0001');
    }

    public function test_admin_can_filter_by_status(): void
    {
        $user = User::factory()->admin()->create();
        $this->actingAs($user);

        Order::factory()->pending()->create(['order_number' => 'ORD-PENDING']);
        Order::factory()->completed()->create(['order_number' => 'ORD-COMPLETED']);

        $response = $this->get(route('admin.orders.index', ['status' => 'pending']));
        $response->assertStatus(200);
        $response->assertSee('ORD-PENDING');
        $response->assertDontSee('ORD-COMPLETED');
    }

    public function test_admin_can_view_order_detail(): void
    {
        $user = User::factory()->admin()->create();
        $this->actingAs($user);

        $order = Order::factory()->create();

        $response = $this->get(route('admin.orders.show', $order));
        $response->assertStatus(200);
    }

    public function test_empty_order_list_shows_message(): void
    {
        $user = User::factory()->admin()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.orders.index'));
        $response->assertSee('Tidak ada pesanan ditemukan');
    }
}
