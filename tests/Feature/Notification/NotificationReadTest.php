<?php

namespace Tests\Feature\Notification;

use App\Models\DatabaseNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationReadTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->customer = User::factory()->create(['role' => 'customer']);
    }

    public function test_notification_marked_as_read(): void
    {
        $notification = DatabaseNotification::factory()->create([
            'user_id' => $this->customer->id,
            'is_read' => false,
        ]);

        $this->actingAs($this->customer)
            ->post(route('customer.notifications.markAsRead', $notification));

        $notification->refresh();
        $this->assertTrue($notification->is_read);
    }

    public function test_mark_all_notifications_as_read(): void
    {
        DatabaseNotification::factory()->count(3)->create([
            'user_id' => $this->customer->id,
            'is_read' => false,
        ]);

        $this->actingAs($this->customer)
            ->post(route('customer.notifications.markAllAsRead'));

        $unreadCount = DatabaseNotification::where('user_id', $this->customer->id)
            ->where('is_read', false)
            ->count();

        $this->assertEquals(0, $unreadCount);
    }

    public function test_unread_count_accurate(): void
    {
        DatabaseNotification::factory()->count(2)->create([
            'user_id' => $this->customer->id,
            'is_read' => false,
        ]);
        DatabaseNotification::factory()->count(1)->create([
            'user_id' => $this->customer->id,
            'is_read' => true,
        ]);

        $count = DatabaseNotification::where('user_id', $this->customer->id)
            ->where('is_read', false)
            ->count();

        $this->assertEquals(2, $count);
    }

    public function test_customer_cannot_mark_other_user_notification_as_read(): void
    {
        $other = User::factory()->create(['role' => 'customer']);
        $notification = DatabaseNotification::factory()->create([
            'user_id' => $other->id,
        ]);

        $this->actingAs($this->customer)
            ->post(route('customer.notifications.markAsRead', $notification))
            ->assertForbidden();
    }

    public function test_mark_all_only_affects_own_notifications(): void
    {
        $other = User::factory()->create(['role' => 'customer']);
        DatabaseNotification::factory()->count(2)->create([
            'user_id' => $other->id,
            'is_read' => false,
        ]);

        $this->actingAs($this->customer)
            ->post(route('customer.notifications.markAllAsRead'));

        $otherUnread = DatabaseNotification::where('user_id', $other->id)
            ->where('is_read', false)
            ->count();

        $this->assertEquals(2, $otherUnread);
    }
}
