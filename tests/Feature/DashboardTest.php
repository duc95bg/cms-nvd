<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Services\DashboardService;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CatalogSeeder::class);
        $this->user = User::factory()->create();
    }

    private function createOrder(array $overrides = []): Order
    {
        return Order::create(array_merge([
            'order_number' => 'ORD-TEST-' . uniqid(),
            'user_id' => $this->user->id,
            'customer_name' => 'Test',
            'customer_email' => 'test@test.com',
            'customer_phone' => '0123456789',
            'customer_address' => 'Test address',
            'payment_method' => 'cod',
            'payment_status' => 'paid',
            'status' => 'confirmed',
            'subtotal' => 100000,
            'total' => 100000,
        ], $overrides));
    }

    public function test_dashboard_page_loads_for_authenticated_user(): void
    {
        $this->actingAs($this->user)
            ->get('/admin/dashboard')
            ->assertStatus(200);
    }

    public function test_dashboard_page_requires_auth(): void
    {
        $this->get('/admin/dashboard')
            ->assertRedirect('/login');
    }

    public function test_dashboard_stats_counts_correctly(): void
    {
        $this->createOrder(['payment_status' => 'paid', 'total' => 150000]);
        $this->createOrder(['payment_status' => 'paid', 'total' => 250000]);
        $this->createOrder(['payment_status' => 'pending', 'status' => 'pending', 'total' => 100000]);

        $stats = DashboardService::getStats();

        $this->assertSame(400000.0, $stats['total_revenue']); // only paid
        $this->assertSame(3, $stats['total_orders']);
        $this->assertSame(1, $stats['pending_orders']);
    }

    public function test_revenue_chart_returns_7_days(): void
    {
        $this->createOrder();

        $chart = DashboardService::getRevenueChart(7);

        $this->assertCount(7, $chart);
        $this->assertArrayHasKey('date', $chart[0]);
        $this->assertArrayHasKey('label', $chart[0]);
        $this->assertArrayHasKey('total', $chart[0]);
    }

    public function test_top_products_excludes_cancelled_orders(): void
    {
        $order = $this->createOrder();
        $order->items()->create([
            'product_id' => 1,
            'product_name' => 'Product A',
            'product_price' => 100000,
            'quantity' => 3,
            'line_total' => 300000,
        ]);

        $cancelledOrder = $this->createOrder(['status' => 'cancelled']);
        $cancelledOrder->items()->create([
            'product_id' => 1,
            'product_name' => 'Product A',
            'product_price' => 100000,
            'quantity' => 10,
            'line_total' => 1000000,
        ]);

        $top = DashboardService::getTopProducts();

        $this->assertCount(1, $top);
        $this->assertEquals(3, $top->first()->total_qty); // excludes cancelled qty 10
    }

    public function test_recent_orders_returns_latest(): void
    {
        for ($i = 0; $i < 15; $i++) {
            $this->createOrder();
        }

        $recent = DashboardService::getRecentOrders(10);

        $this->assertCount(10, $recent);
    }
}
