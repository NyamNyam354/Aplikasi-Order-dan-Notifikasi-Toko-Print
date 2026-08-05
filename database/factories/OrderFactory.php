<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $orderNumber = 'ORD-' . now()->format('Ymd') . '-' . str_pad(fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT);

        return [
            'user_id' => User::factory(),
            'order_number' => $orderNumber,
            'original_filename' => fake()->word() . '.pdf',
            'stored_filename' => fake()->uuid() . '.pdf',
            'file_size' => fake()->numberBetween(1024, 104857600),
            'mime_type' => 'application/pdf',
            'notes' => fake()->sentence(),
            'status' => OrderStatus::PENDING,
            'cancel_reason' => null,
            'completed_at' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::PENDING,
        ]);
    }

    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::PROCESSING,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::COMPLETED,
            'completed_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::CANCELLED,
            'cancel_reason' => 'File tidak sesuai format',
        ]);
    }
}
