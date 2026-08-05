<?php

namespace App\Services;

use App\Models\DatabaseNotification;
use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class NotificationService
{
    public function send(User $user, string $title, string $message, ?Order $order = null): void
    {
        DatabaseNotification::create([
            'user_id' => $user->id,
            'order_id' => $order?->id,
            'type' => 'order_update',
            'title' => $title,
            'message' => $message,
            'is_read' => false,
        ]);
    }

    public function queue(User $user, string $title, string $message, ?Order $order = null): void
    {
        $this->send($user, $title, $message, $order);
    }

    public function markAsRead(DatabaseNotification $notification): void
    {
        $notification->markAsRead();
    }

    public function markAllAsRead(User $user): void
    {
        DatabaseNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
    }

    public function getUnreadCount(User $user): int
    {
        return DatabaseNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();
    }

    public function getNotifications(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return DatabaseNotification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}
