<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        private int $userId,
        private string $title,
        private string $message,
        private ?int $orderId = null,
    ) {}

    public function handle(NotificationService $notificationService): void
    {
        $user = User::find($this->userId);

        if (!$user) {
            Log::warning('Notification job: User not found', ['user_id' => $this->userId]);
            return;
        }

        $order = $this->orderId ? \App\Models\Order::find($this->orderId) : null;

        $notificationService->send($user, $this->title, $this->message, $order);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Failed to send notification', [
            'user_id' => $this->userId,
            'title' => $this->title,
            'error' => $exception->getMessage(),
        ]);
    }
}
