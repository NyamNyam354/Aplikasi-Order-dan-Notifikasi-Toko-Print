<?php

namespace Tests\Feature\Admin;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_change_pending_to_processing(): void
    {
        $user = User::factory()->admin()->create();
        $this->actingAs($user);

        $order = Order::factory()->pending()->create();

        $response = $this->post(route('admin.orders.processing', $order));
        $response->assertRedirect();

        $order->refresh();
        $this->assertEquals(OrderStatus::PROCESSING, $order->status);
    }

    public function test_admin_can_change_processing_to_completed(): void
    {
        $user = User::factory()->admin()->create();
        $this->actingAs($user);

        $order = Order::factory()->processing()->create();

        $response = $this->post(route('admin.orders.complete', $order));
        $response->assertRedirect();

        $order->refresh();
        $this->assertEquals(OrderStatus::COMPLETED, $order->status);
        $this->assertNotNull($order->completed_at);
    }

    public function test_admin_can_cancel_pending_order(): void
    {
        $user = User::factory()->admin()->create();
        $this->actingAs($user);

        $order = Order::factory()->pending()->create();

        $response = $this->post(route('admin.orders.cancel', $order), [
            'cancel_reason' => 'File tidak sesuai format yang diminta',
        ]);
        $response->assertRedirect();

        $order->refresh();
        $this->assertEquals(OrderStatus::CANCELLED, $order->status);
        $this->assertEquals('File tidak sesuai format yang diminta', $order->cancel_reason);
    }

    public function test_cannot_skip_processing_to_complete(): void
    {
        $user = User::factory()->admin()->create();
        $this->actingAs($user);

        $order = Order::factory()->pending()->create();

        $response = $this->post(route('admin.orders.complete', $order));
        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_cannot_complete_already_completed_order(): void
    {
        $user = User::factory()->admin()->create();
        $this->actingAs($user);

        $order = Order::factory()->completed()->create();

        $response = $this->post(route('admin.orders.complete', $order));
        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_cancel_requires_reason(): void
    {
        $user = User::factory()->admin()->create();
        $this->actingAs($user);

        $order = Order::factory()->pending()->create();

        $response = $this->post(route('admin.orders.cancel', $order), [
            'cancel_reason' => '',
        ]);
        $response->assertSessionHasErrors('cancel_reason');
    }

    public function test_cancel_reason_must_be_at_least_10_characters(): void
    {
        $user = User::factory()->admin()->create();
        $this->actingAs($user);

        $order = Order::factory()->pending()->create();

        $response = $this->post(route('admin.orders.cancel', $order), [
            'cancel_reason' => 'Short',
        ]);
        $response->assertSessionHasErrors('cancel_reason');
    }

    public function test_customer_cannot_change_order_status(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $this->actingAs($user);

        $order = Order::factory()->pending()->create(['user_id' => $user->id]);

        $response = $this->post(route('admin.orders.processing', $order));
        $response->assertStatus(403);
    }
}
