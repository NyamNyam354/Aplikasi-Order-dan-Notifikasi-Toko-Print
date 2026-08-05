<?php

namespace App\Policies;

use App\Models\DatabaseNotification;
use App\Models\User;

class NotificationPolicy
{
    public function view(User $user, DatabaseNotification $notification): bool
    {
        return $user->id === $notification->user_id;
    }

    public function markAsRead(User $user, DatabaseNotification $notification): bool
    {
        return $user->id === $notification->user_id;
    }

    public function markAllAsRead(User $user): bool
    {
        return true;
    }
}
