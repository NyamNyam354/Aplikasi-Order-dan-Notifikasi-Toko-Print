<?php

namespace Tests\Feature\Authorization;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerAccessTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->customer = User::factory()->create(['role' => 'customer']);
    }

    public function test_customer_cannot_access_admin_dashboard(): void
    {
        $this->actingAs($this->customer)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_customer_cannot_access_admin_order_list(): void
    {
        $this->actingAs($this->customer)->get(route('admin.orders.index'))->assertForbidden();
    }

    public function test_customer_cannot_access_admin_order_detail(): void
    {
        $order = Order::factory()->create();
        $this->actingAs($this->customer)->get(route('admin.orders.show', $order))->assertForbidden();
    }

    public function test_customer_cannot_change_order_status_to_processing(): void
    {
        $order = Order::factory()->pending()->create();
        $this->actingAs($this->customer)->post(route('admin.orders.processing', $order))->assertForbidden();
    }

    public function test_customer_cannot_change_order_status_to_complete(): void
    {
        $order = Order::factory()->processing()->create();
        $this->actingAs($this->customer)->post(route('admin.orders.complete', $order))->assertForbidden();
    }

    public function test_customer_cannot_cancel_order_via_admin(): void
    {
        $order = Order::factory()->pending()->create();
        $this->actingAs($this->customer)->post(route('admin.orders.cancel', $order), [
            'cancel_reason' => 'Alasan pembatalan yang cukup panjang untuk validasi',
        ])->assertForbidden();
    }

    public function test_customer_cannot_download_via_admin(): void
    {
        $order = Order::factory()->create();
        $this->actingAs($this->customer)->get(route('admin.orders.download', $order))->assertForbidden();
    }

    public function test_guest_redirected_from_customer_routes(): void
    {
        $routes = [
            route('customer.dashboard'),
            route('customer.orders.index'),
            route('customer.orders.create'),
            route('customer.notifications.index'),
        ];

        foreach ($routes as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }
    }
}
