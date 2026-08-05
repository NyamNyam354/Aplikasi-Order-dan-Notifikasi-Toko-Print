<?php

namespace Tests\Feature\Customer;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OrderUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_view_upload_form(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $this->actingAs($user);

        $response = $this->get(route('customer.orders.create'));
        $response->assertStatus(200);
        $response->assertSee('Upload Pesanan');
    }

    public function test_customer_can_upload_valid_file(): void
    {
        Storage::fake('private');
        $user = User::factory()->create(['role' => 'customer']);
        $this->actingAs($user);

        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->post(route('customer.orders.store'), [
            'file' => $file,
            'notes' => 'Tolong cetak warna',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'original_filename' => 'document.pdf',
            'status' => 'pending',
        ]);
    }

    public function test_upload_rejects_invalid_extension(): void
    {
        Storage::fake('private');
        $user = User::factory()->create(['role' => 'customer']);
        $this->actingAs($user);

        $file = UploadedFile::fake()->create('malware.exe', 100, 'application/x-executable');

        $response = $this->post(route('customer.orders.store'), [
            'file' => $file,
        ]);

        $response->assertSessionHasErrors('file');
    }

    public function test_upload_rejects_empty_file(): void
    {
        Storage::fake('private');
        $user = User::factory()->create(['role' => 'customer']);
        $this->actingAs($user);

        $file = UploadedFile::fake()->createWithContent('empty.pdf', '');

        $response = $this->post(route('customer.orders.store'), [
            'file' => $file,
        ]);

        $response->assertSessionHasErrors('file');
    }

    public function test_upload_rejects_file_without_extension(): void
    {
        Storage::fake('private');
        $user = User::factory()->create(['role' => 'customer']);
        $this->actingAs($user);

        $file = UploadedFile::fake()->create('noextension', 100, 'application/pdf');

        $response = $this->post(route('customer.orders.store'), [
            'file' => $file,
        ]);

        $response->assertSessionHasErrors('file');
    }

    public function test_order_number_is_generated(): void
    {
        Storage::fake('private');
        $user = User::factory()->create(['role' => 'customer']);
        $this->actingAs($user);

        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $this->post(route('customer.orders.store'), [
            'file' => $file,
            'notes' => 'Test',
        ]);

        $order = Order::where('user_id', $user->id)->first();
        $this->assertStringStartsWith('ORD-', $order->order_number);
    }

    public function test_guest_cannot_upload(): void
    {
        $response = $this->post(route('customer.orders.store'), [
            'file' => UploadedFile::fake()->create('test.pdf', 100, 'application/pdf'),
        ]);

        $response->assertRedirect(route('login'));
    }
}
