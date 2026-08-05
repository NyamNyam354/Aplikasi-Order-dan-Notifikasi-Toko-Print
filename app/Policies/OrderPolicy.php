<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->role === UserRole::ADMIN) {
            return true;
        }

        return null;
    }

    public function view(User $user, Order $order): bool
    {
        return $user->id === $order->user_id;
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::CUSTOMER;
    }

    public function updateStatus(User $user, Order $order): bool
    {
        return $user->role === UserRole::ADMIN;
    }

    public function download(User $user, Order $order): bool
    {
        return $user->role === UserRole::ADMIN || $user->id === $order->user_id;
    }

    public function cancel(User $user, Order $order): bool
    {
        return $user->role === UserRole::ADMIN;
    }

    public function process(User $user, Order $order): bool
    {
        return $user->role === UserRole::ADMIN;
    }

    public function complete(User $user, Order $order): bool
    {
        return $user->role === UserRole::ADMIN;
    }
}
