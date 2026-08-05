<?php

namespace Tests\Unit\Services;

use App\Models\DatabaseNotification;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    private NotificationService $notificationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->notificationService = app(NotificationService::class);
    }

    public function test_send_creates_notification(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        $this->notificationService->send($user, 'Test Title', 'Test Message');

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'title' => 'Test Title',
        ]);
    }

    public function test_send_sets_is_read_false(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        $this->notificationService->send($user, 'Test Title', 'Test Message');

        $notification = DatabaseNotification::where('user_id', $user->id)->first();
        $this->assertFalse($notification->is_read);
    }

    public function test_mark_as_read(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $notification = DatabaseNotification::factory()->create([
            'user_id' => $user->id,
            'is_read' => false,
        ]);

        $this->notificationService->markAsRead($notification);

        $notification->refresh();
        $this->assertTrue($notification->is_read);
        $this->assertNotNull($notification->read_at);
    }

    public function test_mark_all_as_read(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        DatabaseNotification::factory()->count(3)->create([
            'user_id' => $user->id,
            'is_read' => false,
        ]);

        $this->notificationService->markAllAsRead($user);

        $unreadCount = DatabaseNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();
        $this->assertEquals(0, $unreadCount);
    }

    public function test_get_unread_count(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        DatabaseNotification::factory()->count(2)->create([
            'user_id' => $user->id,
            'is_read' => false,
        ]);
        DatabaseNotification::factory()->count(1)->create([
            'user_id' => $user->id,
            'is_read' => true,
        ]);

        $count = $this->notificationService->getUnreadCount($user);

        $this->assertEquals(2, $count);
    }

    public function test_get_notifications_returns_paginator(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        DatabaseNotification::factory()->count(5)->create(['user_id' => $user->id]);

        $result = $this->notificationService->getNotifications($user);

        $this->assertInstanceOf(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class, $result);
    }

    public function test_get_notifications_only_returns_own(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $other = User::factory()->create(['role' => 'customer']);
        DatabaseNotification::factory()->count(3)->create(['user_id' => $user->id]);
        DatabaseNotification::factory()->count(2)->create(['user_id' => $other->id]);

        $result = $this->notificationService->getNotifications($user);

        $this->assertEquals(3, $result->total());
    }

    public function test_mark_all_only_affects_own_notifications(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $other = User::factory()->create(['role' => 'customer']);
        DatabaseNotification::factory()->count(2)->create([
            'user_id' => $other->id,
            'is_read' => false,
        ]);

        $this->notificationService->markAllAsRead($user);

        $otherUnread = DatabaseNotification::where('user_id', $other->id)
            ->where('is_read', false)
            ->count();
        $this->assertEquals(2, $otherUnread);
    }
}
