<?php

namespace Tests\Feature\Authorization;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_admin_cannot_access_customer_dashboard(): void
    {
        $this->actingAs($this->admin)->get(route('customer.dashboard'))->assertForbidden();
    }

    public function test_admin_cannot_access_customer_order_list(): void
    {
        $this->actingAs($this->admin)->get(route('customer.orders.index'))->assertForbidden();
    }

    public function test_admin_cannot_access_customer_upload_form(): void
    {
        $this->actingAs($this->admin)->get(route('customer.orders.create'))->assertForbidden();
    }

    public function test_admin_cannot_upload_order(): void
    {
        $file = \Illuminate\Http\UploadedFile::fake()->create('test.pdf', 100, 'application/pdf');
        $this->actingAs($this->admin)->post(route('customer.orders.store'), [
            'file' => $file,
        ])->assertForbidden();
    }

    public function test_admin_cannot_view_customer_order_detail(): void
    {
        $order = Order::factory()->create();
        $this->actingAs($this->admin)->get(route('customer.orders.show', $order))->assertForbidden();
    }

    public function test_admin_cannot_access_customer_notifications(): void
    {
        $this->actingAs($this->admin)->get(route('customer.notifications.index'))->assertForbidden();
    }

    public function test_admin_cannot_mark_customer_notification_as_read(): void
    {
        $notification = \App\Models\DatabaseNotification::factory()->create();
        $this->actingAs($this->admin)->post(route('customer.notifications.markAsRead', $notification))->assertForbidden();
    }

    public function test_guest_redirected_from_admin_routes(): void
    {
        $order = Order::factory()->create();
        $routes = [
            route('admin.dashboard'),
            route('admin.orders.index'),
            route('admin.orders.show', $order),
        ];

        foreach ($routes as $url) {
            $this->get($url)->assertRedirect(route('admin.login'));
        }
    }
}
