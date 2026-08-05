<?php

namespace Tests\Feature\EdgeCase;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EdgeCaseTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private');
        $this->admin = User::factory()->admin()->create();
    }

    public function test_upload_rejects_file_larger_than_100mb(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $this->actingAs($user);

        $file = UploadedFile::fake()->create('large.pdf', 102401, 'application/pdf');

        $this->post(route('customer.orders.store'), ['file' => $file])
            ->assertSessionHasErrors('file');
    }

    public function test_upload_rejects_php_extension(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $this->actingAs($user);

        $file = new UploadedFile(
            tempnam(sys_get_temp_dir(), 'test'),
            'shell.php',
            'application/x-php',
            null,
            true
        );

        $this->post(route('customer.orders.store'), ['file' => $file])
            ->assertSessionHasErrors('file');
    }

    public function test_upload_rejects_exe_extension(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $this->actingAs($user);

        $file = new UploadedFile(
            tempnam(sys_get_temp_dir(), 'test'),
            'malware.exe',
            'application/x-msdownload',
            null,
            true
        );

        $this->post(route('customer.orders.store'), ['file' => $file])
            ->assertSessionHasErrors('file');
    }

    public function test_upload_rejects_double_extension_attack(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $this->actingAs($user);

        $file = new UploadedFile(
            tempnam(sys_get_temp_dir(), 'test'),
            'file.pdf.exe',
            'application/x-msdownload',
            null,
            true
        );

        $this->post(route('customer.orders.store'), ['file' => $file])
            ->assertSessionHasErrors('file');
    }

    public function test_upload_rejects_text_file(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $this->actingAs($user);

        $file = new UploadedFile(
            tempnam(sys_get_temp_dir(), 'test'),
            'notes.txt',
            'text/plain',
            null,
            true
        );

        $this->post(route('customer.orders.store'), ['file' => $file])
            ->assertSessionHasErrors('file');
    }

    public function test_upload_rejects_svg_file(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $this->actingAs($user);

        $file = new UploadedFile(
            tempnam(sys_get_temp_dir(), 'test'),
            'image.svg',
            'image/svg+xml',
            null,
            true
        );

        $this->post(route('customer.orders.store'), ['file' => $file])
            ->assertSessionHasErrors('file');
    }

    public function test_double_click_creates_only_one_order(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $this->actingAs($user);

        $file1 = UploadedFile::fake()->create('doc1.pdf', 100, 'application/pdf');
        $file2 = UploadedFile::fake()->create('doc2.pdf', 100, 'application/pdf');

        $this->post(route('customer.orders.store'), ['file' => $file1]);
        $this->post(route('customer.orders.store'), ['file' => $file2]);

        $this->assertEquals(2, Order::where('user_id', $user->id)->count());
    }

    public function test_get_request_to_login_page_returns_200(): void
    {
        $response = $this->get(route('login'));
        $response->assertOk();
    }

    public function test_admin_complete_order_twice_does_not_duplicate(): void
    {
        $order = Order::factory()->processing()->create();
        $this->actingAs($this->admin);

        $this->post(route('admin.orders.complete', $order));
        $this->post(route('admin.orders.complete', $order));

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'completed',
        ]);
    }

    public function test_guest_cannot_access_protected_route(): void
    {
        $this->get(route('customer.dashboard'))->assertRedirect(route('login'));
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
    }

    public function test_customer_cannot_access_other_users_order(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $other = User::factory()->create(['role' => 'customer']);
        $order = Order::factory()->create(['user_id' => $other->id]);

        $this->actingAs($user)->get(route('customer.orders.show', $order))->assertForbidden();
    }

    public function test_admin_access_nonexistent_order_returns_404(): void
    {
        $this->actingAs($this->admin)->get(route('admin.orders.show', 999999))->assertNotFound();
    }

    public function test_customer_access_nonexistent_order_returns_404(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $this->actingAs($user)->get(route('customer.orders.show', 999999))->assertNotFound();
    }
}
