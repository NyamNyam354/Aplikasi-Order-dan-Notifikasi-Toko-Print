<?php

namespace Tests\Feature\Customer;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_view_order_history(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $this->actingAs($user);

        Order::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->get(route('customer.orders.index'));
        $response->assertStatus(200);
    }

    public function test_customer_can_only_see_own_orders(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $otherUser = User::factory()->create(['role' => 'customer']);
        $this->actingAs($user);

        Order::factory()->create(['user_id' => $user->id, 'order_number' => 'ORD-MY-ORDER']);
        Order::factory()->create(['user_id' => $otherUser->id, 'order_number' => 'ORD-OTHER-ORDER']);

        $response = $this->get(route('customer.orders.index'));
        $response->assertSee('ORD-MY-ORDER');
        $response->assertDontSee('ORD-OTHER-ORDER');
    }

    public function test_customer_can_view_order_detail(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $this->actingAs($user);

        $order = Order::factory()->create(['user_id' => $user->id]);

        $response = $this->get(route('customer.orders.show', $order));
        $response->assertStatus(200);
    }

    public function test_customer_cannot_view_other_user_order(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $otherUser = User::factory()->create(['role' => 'customer']);
        $this->actingAs($user);

        $order = Order::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->get(route('customer.orders.show', $order));
        $response->assertStatus(403);
    }

    public function test_empty_order_history_shows_message(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $this->actingAs($user);

        $response = $this->get(route('customer.orders.index'));
        $response->assertSee('Belum ada pesanan');
    }
}
