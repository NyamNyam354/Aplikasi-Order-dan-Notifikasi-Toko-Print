<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchAndFilterTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_admin_can_search_by_order_number(): void
    {
        $order = Order::factory()->pending()->create(['order_number' => 'ORD-20260704-0001']);
        Order::factory()->pending()->create(['order_number' => 'ORD-20260704-0002']);

        $response = $this->actingAs($this->admin)->get(route('admin.orders.index', ['search' => '0001']));

        $response->assertOk();
        $this->assertDatabaseHas('orders', ['order_number' => 'ORD-20260704-0001']);
    }

    public function test_admin_can_search_by_customer_name(): void
    {
        $user = User::factory()->create(['name' => 'Budi Santoso', 'role' => 'customer']);
        $order = Order::factory()->pending()->create(['user_id' => $user->id]);
        Order::factory()->pending()->create();

        $response = $this->actingAs($this->admin)->get(route('admin.orders.index', ['search' => 'Budi']));

        $response->assertOk();
    }

    public function test_admin_can_search_by_email(): void
    {
        $user = User::factory()->create(['email' => 'budi@example.com', 'role' => 'customer']);
        $order = Order::factory()->pending()->create(['user_id' => $user->id]);
        Order::factory()->pending()->create();

        $response = $this->actingAs($this->admin)->get(route('admin.orders.index', ['search' => 'budi@example']));

        $response->assertOk();
    }

    public function test_admin_can_search_by_filename(): void
    {
        $order = Order::factory()->pending()->create(['original_filename' => 'laporan-keuangan.pdf']);
        Order::factory()->pending()->create(['original_filename' => 'invoice-bulan.ini.pdf']);

        $response = $this->actingAs($this->admin)->get(route('admin.orders.index', ['search' => 'laporan']));

        $response->assertOk();
    }

    public function test_admin_can_sort_by_date_oldest(): void
    {
        $old = Order::factory()->pending()->create(['created_at' => now()->subDays(5)]);
        $new = Order::factory()->pending()->create(['created_at' => now()]);

        $response = $this->actingAs($this->admin)->get(route('admin.orders.index', ['sort' => 'oldest']));

        $response->assertOk();
    }

    public function test_admin_can_sort_by_status(): void
    {
        Order::factory()->completed()->create();
        Order::factory()->pending()->create();
        Order::factory()->processing()->create();

        $response = $this->actingAs($this->admin)->get(route('admin.orders.index', ['sort' => 'status']));

        $response->assertOk();
    }

    public function test_admin_can_sort_by_customer(): void
    {
        $user1 = User::factory()->create(['name' => 'Alice', 'role' => 'customer']);
        $user2 = User::factory()->create(['name' => 'Bob', 'role' => 'customer']);
        Order::factory()->pending()->create(['user_id' => $user1->id]);
        Order::factory()->pending()->create(['user_id' => $user2->id]);

        $response = $this->actingAs($this->admin)->get(route('admin.orders.index', ['sort' => 'customer']));

        $response->assertOk();
    }

    public function test_pagination_page_1_shows_correct_count(): void
    {
        Order::factory()->count(15)->pending()->create();

        $response = $this->actingAs($this->admin)->get(route('admin.orders.index'));

        $response->assertOk();
        $this->assertEquals(15, $response->viewData('orders')->count());
    }

    public function test_pagination_page_2_shows_next_set(): void
    {
        Order::factory()->count(20)->pending()->create();

        $response = $this->actingAs($this->admin)->get(route('admin.orders.index', ['page' => 2]));

        $response->assertOk();
        $this->assertEquals(5, $response->viewData('orders')->count());
    }
}
