<?php

namespace Tests\Unit\Enums;

use App\Enums\OrderStatus;
use PHPUnit\Framework\TestCase;

class OrderStatusTest extends TestCase
{
    public function test_all_statuses_exist(): void
    {
        $this->assertEquals('pending', OrderStatus::PENDING->value);
        $this->assertEquals('processing', OrderStatus::PROCESSING->value);
        $this->assertEquals('completed', OrderStatus::COMPLETED->value);
        $this->assertEquals('cancelled', OrderStatus::CANCELLED->value);
    }

    public function test_status_has_label(): void
    {
        $this->assertNotEmpty(OrderStatus::PENDING->label());
        $this->assertNotEmpty(OrderStatus::PROCESSING->label());
        $this->assertNotEmpty(OrderStatus::COMPLETED->label());
        $this->assertNotEmpty(OrderStatus::CANCELLED->label());
    }

    public function test_status_has_color(): void
    {
        $this->assertNotEmpty(OrderStatus::PENDING->color());
        $this->assertNotEmpty(OrderStatus::PROCESSING->color());
        $this->assertNotEmpty(OrderStatus::COMPLETED->color());
        $this->assertNotEmpty(OrderStatus::CANCELLED->color());
    }

    public function test_pending_label_is_indonesian(): void
    {
        $this->assertEquals('Pending', OrderStatus::PENDING->label());
    }

    public function test_completed_label_is_indonesian(): void
    {
        $this->assertStringContainsString('Selesai', OrderStatus::COMPLETED->label());
    }

    public function test_cancelled_label_is_indonesian(): void
    {
        $this->assertStringContainsString('Dibatalkan', OrderStatus::CANCELLED->label());
    }

    public function test_status_from_value(): void
    {
        $this->assertEquals(OrderStatus::PENDING, OrderStatus::from('pending'));
        $this->assertEquals(OrderStatus::PROCESSING, OrderStatus::from('processing'));
        $this->assertEquals(OrderStatus::COMPLETED, OrderStatus::from('completed'));
        $this->assertEquals(OrderStatus::CANCELLED, OrderStatus::from('cancelled'));
    }

    public function test_status_try_from_value(): void
    {
        $this->assertEquals(OrderStatus::PENDING, OrderStatus::tryFrom('pending'));
        $this->assertNull(OrderStatus::tryFrom('invalid'));
    }
}
