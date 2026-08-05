<?php

namespace Tests\Unit\Services;

use App\Constants\OrderConstant;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    private OrderService $orderService;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private');
        $this->orderService = app(OrderService::class);
    }

    public function test_generate_order_number_format(): void
    {
        $orderNumber = $this->orderService->generateOrderNumber();

        $this->assertMatchesRegularExpression('/^ORD-\d{8}-\d{4}$/', $orderNumber);
    }

    public function test_generate_order_number_increments(): void
    {
        $first = $this->orderService->generateOrderNumber();

        Order::factory()->create(['order_number' => $first]);

        $second = $this->orderService->generateOrderNumber();

        $this->assertNotEquals($first, $second);
    }

    public function test_create_order_creates_record(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $file = UploadedFile::fake()->create('test.pdf', 100, 'application/pdf');

        $order = $this->orderService->createOrder($user, $file, 'Notes');

        $this->assertNotNull($order);
        $this->assertEquals($user->id, $order->user_id);
        $this->assertEquals(OrderStatus::PENDING, $order->status);
        $this->assertDatabaseHas('orders', ['id' => $order->id]);
    }

    public function test_create_order_stores_file_metadata(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $file = UploadedFile::fake()->create('document.pdf', 200, 'application/pdf');

        $order = $this->orderService->createOrder($user, $file, null);

        $this->assertEquals('document.pdf', $order->original_filename);
        $this->assertNotNull($order->stored_filename);
        $this->assertGreaterThan(0, $order->file_size);
        $this->assertEquals('application/pdf', $order->mime_type);
    }

    public function test_change_status_to_processing(): void
    {
        $order = Order::factory()->pending()->create();

        $result = $this->orderService->processing($order);

        $this->assertEquals(OrderStatus::PROCESSING, $result->status);
    }

    public function test_change_status_to_completed(): void
    {
        $order = Order::factory()->processing()->create();

        $result = $this->orderService->complete($order);

        $this->assertEquals(OrderStatus::COMPLETED, $result->status);
        $this->assertNotNull($result->completed_at);
    }

    public function test_change_status_to_cancelled(): void
    {
        $order = Order::factory()->pending()->create();

        $result = $this->orderService->cancel($order, 'Alasan pembatalan untuk testing');

        $this->assertEquals(OrderStatus::CANCELLED, $result->status);
        $this->assertEquals('Alasan pembatalan untuk testing', $result->cancel_reason);
    }

    public function test_invalid_transition_throws_exception(): void
    {
        $order = Order::factory()->completed()->create();

        $this->expectException(\InvalidArgumentException::class);
        $this->orderService->processing($order);
    }

    public function test_cannot_skip_pending_to_completed(): void
    {
        $order = Order::factory()->pending()->create();

        $this->expectException(\InvalidArgumentException::class);
        $this->orderService->complete($order);
    }

    public function test_cannot_cancel_processing_order(): void
    {
        $order = Order::factory()->processing()->create();

        $this->expectException(\InvalidArgumentException::class);
        $this->orderService->cancel($order, 'Alasan pembatalan untuk testing');
    }

    public function test_get_orders_returns_paginator(): void
    {
        Order::factory()->count(5)->pending()->create();

        $result = $this->orderService->getOrders();

        $this->assertInstanceOf(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class, $result);
    }

    public function test_get_orders_with_search(): void
    {
        Order::factory()->create(['order_number' => 'ORD-20260704-0001']);
        Order::factory()->create(['order_number' => 'ORD-20260704-0002']);

        $result = $this->orderService->getOrders(search: '0001');

        $this->assertEquals(1, $result->total());
    }

    public function test_get_orders_with_status_filter(): void
    {
        Order::factory()->count(3)->pending()->create();
        Order::factory()->count(2)->completed()->create();

        $result = $this->orderService->getOrders(status: 'pending');

        $this->assertEquals(3, $result->total());
    }

    public function test_get_user_orders_only_returns_own(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        Order::factory()->count(3)->create(['user_id' => $user->id]);
        Order::factory()->count(2)->create();

        $result = $this->orderService->getUserOrders($user);

        $this->assertEquals(3, $result->total());
    }

    public function test_get_dashboard_stats(): void
    {
        Order::factory()->count(2)->pending()->create();
        Order::factory()->count(1)->completed()->create();

        $stats = $this->orderService->getDashboardStats();

        $this->assertArrayHasKey('pending', $stats);
        $this->assertArrayHasKey('completed', $stats);
        $this->assertEquals(2, $stats['pending']);
        $this->assertEquals(1, $stats['completed']);
    }
}
