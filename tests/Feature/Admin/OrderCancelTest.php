<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderCancelTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_admin_can_cancel_pending_order(): void
    {
        $order = Order::factory()->pending()->create();

        $response = $this->actingAs($this->admin)->post(route('admin.orders.cancel', $order), [
            'cancel_reason' => 'Alasan pembatalan yang cukup panjang untuk validasi',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_admin_cannot_cancel_processing_order(): void
    {
        $order = Order::factory()->processing()->create();

        $response = $this->actingAs($this->admin)->post(route('admin.orders.cancel', $order), [
            'cancel_reason' => 'Alasan pembatalan yang cukup panjang untuk validasi',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'processing',
        ]);
    }

    public function test_admin_cannot_cancel_completed_order(): void
    {
        $order = Order::factory()->completed()->create();

        $response = $this->actingAs($this->admin)->post(route('admin.orders.cancel', $order), [
            'cancel_reason' => 'Alasan pembatalan yang cukup panjang untuk validasi',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'completed',
        ]);
    }

    public function test_cancel_requires_reason(): void
    {
        $order = Order::factory()->pending()->create();

        $response = $this->actingAs($this->admin)->post(route('admin.orders.cancel', $order), [
            'cancel_reason' => '',
        ]);

        $response->assertSessionHasErrors('cancel_reason');
    }

    public function test_cancel_reason_must_be_at_least_10_characters(): void
    {
        $order = Order::factory()->pending()->create();

        $response = $this->actingAs($this->admin)->post(route('admin.orders.cancel', $order), [
            'cancel_reason' => 'Short',
        ]);

        $response->assertSessionHasErrors('cancel_reason');
    }

    public function test_cancelled_order_has_cancel_reason(): void
    {
        $order = Order::factory()->pending()->create();
        $reason = 'Alasan pembatalan yang sangat jelas dan panjang untuk keperluan testing';

        $this->actingAs($this->admin)->post(route('admin.orders.cancel', $order), [
            'cancel_reason' => $reason,
        ]);

        $order->refresh();
        $this->assertEquals($reason, $order->cancel_reason);
    }

    public function test_customer_cannot_cancel_order_via_admin(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $order = Order::factory()->pending()->create();

        $response = $this->actingAs($user)->post(route('admin.orders.cancel', $order), [
            'cancel_reason' => 'Alasan pembatalan yang cukup panjang untuk validasi',
        ]);

        $response->assertForbidden();
    }

    public function test_cannot_cancel_already_cancelled_order(): void
    {
        $order = Order::factory()->cancelled()->create();

        $response = $this->actingAs($this->admin)->post(route('admin.orders.cancel', $order), [
            'cancel_reason' => 'Alasan pembatalan yang cukup panjang untuk validasi',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'cancelled',
        ]);
    }
}
