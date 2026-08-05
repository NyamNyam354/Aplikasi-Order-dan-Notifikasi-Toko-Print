<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DownloadTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private');
        $this->admin = User::factory()->admin()->create();
    }

    public function test_admin_can_download_order_file(): void
    {
        $this->actingAs($this->admin);

        $user = User::factory()->create(['role' => 'customer']);
        $order = Order::factory()->create(['user_id' => $user->id]);

        Storage::disk('private')->put('orders/'.$order->stored_filename, 'fake file content');

        $response = $this->get(route('admin.orders.download', $order));

        $response->assertStatus(200);
    }

    public function test_customer_cannot_download_via_admin_route(): void
    {
        $order = Order::factory()->create();
        $user = User::factory()->create(['role' => 'customer']);

        $this->actingAs($user)->get(route('admin.orders.download', $order))->assertForbidden();
    }

    public function test_guest_cannot_download_via_admin_route(): void
    {
        $order = Order::factory()->create();
        $this->get(route('admin.orders.download', $order))->assertRedirect(route('admin.login'));
    }

    public function test_download_nonexistent_file_shows_error(): void
    {
        $this->actingAs($this->admin);

        $order = Order::factory()->create([
            'stored_filename' => 'nonexistent-file-'.uniqid().'.pdf',
        ]);

        $this->get(route('admin.orders.download', $order))
            ->assertRedirect()
            ->assertSessionHas('error');
    }
}
