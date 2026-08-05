<?php

namespace Tests\Feature\Customer;

use App\Models\DatabaseNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_view_notifications(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $this->actingAs($user);

        $response = $this->get(route('customer.notifications.index'));
        $response->assertStatus(200);
    }

    public function test_customer_can_mark_notification_as_read(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $this->actingAs($user);

        $notification = DatabaseNotification::factory()->create([
            'user_id' => $user->id,
            'is_read' => false,
        ]);

        $response = $this->post(route('customer.notifications.markAsRead', $notification));
        $response->assertRedirect();

        $notification->refresh();
        $this->assertTrue($notification->is_read);
    }

    public function test_customer_can_mark_all_as_read(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $this->actingAs($user);

        DatabaseNotification::factory()->count(3)->create([
            'user_id' => $user->id,
            'is_read' => false,
        ]);

        $response = $this->post(route('customer.notifications.markAllAsRead'));
        $response->assertRedirect();

        $unreadCount = DatabaseNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();
        $this->assertEquals(0, $unreadCount);
    }

    public function test_customer_cannot_mark_other_user_notification(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $otherUser = User::factory()->create(['role' => 'customer']);
        $this->actingAs($user);

        $notification = DatabaseNotification::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->post(route('customer.notifications.markAsRead', $notification));
        $response->assertStatus(403);
    }
}
