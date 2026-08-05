<?php

namespace Database\Factories;

use App\Models\DatabaseNotification;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DatabaseNotification>
 */
class DatabaseNotificationFactory extends Factory
{
    protected $model = DatabaseNotification::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'order_id' => null,
            'type' => 'order_update',
            'title' => fake()->sentence(3),
            'message' => fake()->sentence(),
            'data' => null,
            'is_read' => false,
            'read_at' => null,
        ];
    }

    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_read' => true,
            'read_at' => now(),
        ]);
    }
}
