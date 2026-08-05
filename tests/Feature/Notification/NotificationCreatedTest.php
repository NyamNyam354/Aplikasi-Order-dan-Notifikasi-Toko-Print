<?php

namespace Tests\Feature\Notification;

use App\Models\DatabaseNotification;
use App\Models\Order;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class NotificationCreatedTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_notification_created_when_order_is_received(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        $order = Order::factory()->pending()->create(['user_id' => $user->id]);

        app(NotificationService::class)->send(
            $user,
            'Pesanan Diterima',
            "Pesanan {$order->order_number} telah diterima dan akan segera kami proses.",
            $order
        );

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'title' => 'Pesanan Diterima',
            'is_read' => false,
        ]);
    }

    public function test_notification_created_when_order_is_processed(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $order = Order::factory()->pending()->create(['user_id' => $user->id]);
        $this->actingAs($this->admin);

        $this->post(route('admin.orders.processing', $order));

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'title' => 'Pesanan Diproses',
        ]);
    }

    public function test_notification_created_when_order_is_completed(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $order = Order::factory()->processing()->create(['user_id' => $user->id]);
        $this->actingAs($this->admin);

        $this->post(route('admin.orders.complete', $order));

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'title' => 'Pesanan Selesai',
        ]);
    }

    public function test_notification_created_when_order_is_cancelled(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $order = Order::factory()->pending()->create(['user_id' => $user->id]);
        $this->actingAs($this->admin);

        $this->post(route('admin.orders.cancel', $order), [
            'cancel_reason' => 'Alasan pembatalan yang cukup panjang untuk validasi',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'title' => 'Pesanan Dibatalkan',
        ]);
    }

    public function test_notification_contains_correct_order_number(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $order = Order::factory()->pending()->create(['user_id' => $user->id]);
        $this->actingAs($this->admin);

        $this->post(route('admin.orders.processing', $order));

        $notification = DatabaseNotification::where('user_id', $user->id)
            ->where('title', 'Pesanan Diproses')
            ->first();

        $this->assertNotNull($notification);
        $this->assertStringContainsString($order->order_number, $notification->message);
    }

    public function test_notification_is_linked_to_order(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $order = Order::factory()->pending()->create(['user_id' => $user->id]);
        $this->actingAs($this->admin);

        $this->post(route('admin.orders.processing', $order));

        $notification = DatabaseNotification::where('user_id', $user->id)
            ->where('title', 'Pesanan Diproses')
            ->first();

        $this->assertNotNull($notification);
        $this->assertEquals($order->id, $notification->order_id);
    }
}
