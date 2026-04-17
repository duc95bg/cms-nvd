<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\CartService;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CatalogSeeder::class);
        $this->user = User::factory()->create();
    }

    private function getProductAndVariant(): array
    {
        $product = Product::active()->whereHas('variants')->first();
        $variant = $product->variants()->active()->where('stock', '>', 0)->first();

        return [$product, $variant];
    }

    // ── Cart ──

    public function test_add_to_cart_stores_item_in_session(): void
    {
        [$product, $variant] = $this->getProductAndVariant();

        $this->post('/cart/add', [
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'qty' => 1,
        ])->assertRedirect()->assertSessionHas('success');

        $cart = session('cart');
        $key = "{$product->id}-{$variant->id}";
        $this->assertArrayHasKey($key, $cart);
        $this->assertSame(1, $cart[$key]['quantity']);
    }

    public function test_add_to_cart_validates_stock(): void
    {
        [$product, $variant] = $this->getProductAndVariant();

        $this->post('/cart/add', [
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'qty' => 99999,
        ])->assertRedirect()->assertSessionHas('error');
    }

    public function test_add_to_cart_requires_valid_product(): void
    {
        $this->post('/cart/add', [
            'product_id' => 99999,
            'variant_id' => null,
            'qty' => 1,
        ])->assertSessionHasErrors('product_id');
    }

    public function test_cart_update_quantity(): void
    {
        [$product, $variant] = $this->getProductAndVariant();
        $key = "{$product->id}-{$variant->id}";

        // Add item first
        session(['cart' => [$key => ['product_id' => $product->id, 'variant_id' => $variant->id, 'quantity' => 1]]]);

        $this->post('/cart/update', [
            'key' => $key,
            'qty' => 3,
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertSame(3, session("cart.{$key}.quantity"));
    }

    public function test_cart_remove_item(): void
    {
        [$product, $variant] = $this->getProductAndVariant();
        $key = "{$product->id}-{$variant->id}";

        session(['cart' => [$key => ['product_id' => $product->id, 'variant_id' => $variant->id, 'quantity' => 1]]]);

        $this->post('/cart/remove', ['key' => $key])
            ->assertRedirect()->assertSessionHas('success');

        $this->assertArrayNotHasKey($key, session('cart', []));
    }

    public function test_cart_page_shows_items_and_total(): void
    {
        [$product, $variant] = $this->getProductAndVariant();
        $key = "{$product->id}-{$variant->id}";

        session(['cart' => [$key => ['product_id' => $product->id, 'variant_id' => $variant->id, 'quantity' => 2]]]);

        $this->get('/vi/cart')
            ->assertStatus(200)
            ->assertSee($product->name['vi'], false);
    }

    // ── Checkout ──

    public function test_checkout_page_requires_non_empty_cart(): void
    {
        $this->get('/vi/checkout')
            ->assertRedirect();
    }

    public function test_checkout_cod_creates_order_and_decrements_stock(): void
    {
        [$product, $variant] = $this->getProductAndVariant();
        $key = "{$product->id}-{$variant->id}";
        $originalStock = $variant->stock;

        session(['cart' => [$key => ['product_id' => $product->id, 'variant_id' => $variant->id, 'quantity' => 2]]]);

        $this->post('/checkout/process', [
            'customer_name' => 'Test User',
            'customer_email' => 'test@example.com',
            'customer_phone' => '0912345678',
            'customer_address' => '123 Test Street',
            'payment_method' => 'cod',
        ])->assertRedirect();

        // Order created
        $order = Order::latest()->first();
        $this->assertNotNull($order);
        $this->assertSame('cod', $order->payment_method);
        $this->assertSame('pending', $order->status);
        $this->assertSame(1, $order->items()->count());
        $this->assertSame(2, $order->items->first()->quantity);

        // Stock decremented
        $this->assertSame($originalStock - 2, $variant->fresh()->stock);

        // Cart cleared
        $this->assertTrue(app(CartService::class)->isEmpty());
    }

    public function test_checkout_bank_transfer_shows_bank_info(): void
    {
        [$product, $variant] = $this->getProductAndVariant();
        $key = "{$product->id}-{$variant->id}";

        session(['cart' => [$key => ['product_id' => $product->id, 'variant_id' => $variant->id, 'quantity' => 1]]]);

        config([
            'payment.bank_name' => 'TestBank',
            'payment.bank_account' => '9999',
            'payment.bank_holder' => 'TEST HOLDER',
        ]);

        $response = $this->post('/checkout/process', [
            'customer_name' => 'Bank User',
            'customer_email' => 'bank@example.com',
            'customer_phone' => '0912345678',
            'customer_address' => '456 Bank Street',
            'payment_method' => 'bank_transfer',
        ]);

        $order = Order::latest()->first();
        $response->assertRedirect("/vi/order/bank-transfer/{$order->id}");
    }

    public function test_order_items_snapshot_product_data(): void
    {
        [$product, $variant] = $this->getProductAndVariant();
        $key = "{$product->id}-{$variant->id}";

        session(['cart' => [$key => ['product_id' => $product->id, 'variant_id' => $variant->id, 'quantity' => 1]]]);

        $this->post('/checkout/process', [
            'customer_name' => 'Snapshot Test',
            'customer_email' => 'snap@example.com',
            'customer_phone' => '0912345678',
            'customer_address' => '789 Snap Ave',
            'payment_method' => 'cod',
        ]);

        $item = Order::latest()->first()->items->first();
        $this->assertNotEmpty($item->product_name);
        $this->assertIsNumeric($item->product_price);
        $this->assertSame(1, $item->quantity);
    }

    // ── Admin Orders ──

    public function test_admin_order_list_shows_orders(): void
    {
        Order::create([
            'order_number' => 'ORD-TEST-001',
            'customer_name' => 'Admin Test',
            'customer_email' => 'admin@test.com',
            'customer_phone' => '0912345678',
            'customer_address' => 'Test Addr',
            'payment_method' => 'cod',
            'subtotal' => 100000,
            'total' => 100000,
        ]);

        $this->actingAs($this->user)
            ->get('/admin/orders')
            ->assertStatus(200)
            ->assertSee('ORD-TEST-001');
    }

    public function test_admin_order_status_update_with_log(): void
    {
        $order = Order::create([
            'order_number' => 'ORD-TEST-002',
            'customer_name' => 'Status Test',
            'customer_email' => 'status@test.com',
            'customer_phone' => '0912345678',
            'customer_address' => 'Test',
            'payment_method' => 'cod',
            'status' => 'pending',
            'subtotal' => 100000,
            'total' => 100000,
        ]);

        $this->actingAs($this->user)
            ->post("/admin/orders/{$order->id}/status", [
                'status' => 'confirmed',
                'note' => 'Verified payment',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('confirmed', $order->fresh()->status);

        $log = OrderStatusLog::where('order_id', $order->id)->latest()->first();
        $this->assertSame('pending', $log->old_status);
        $this->assertSame('confirmed', $log->new_status);
        $this->assertSame('Verified payment', $log->note);
    }

    public function test_admin_order_status_invalid_transition_rejected(): void
    {
        $order = Order::create([
            'order_number' => 'ORD-TEST-003',
            'customer_name' => 'Invalid Test',
            'customer_email' => 'invalid@test.com',
            'customer_phone' => '0912345678',
            'customer_address' => 'Test',
            'payment_method' => 'cod',
            'status' => 'pending',
            'subtotal' => 100000,
            'total' => 100000,
        ]);

        $this->actingAs($this->user)
            ->post("/admin/orders/{$order->id}/status", [
                'status' => 'delivered',
            ])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame('pending', $order->fresh()->status);
    }

    // ── User Order History ──

    public function test_user_order_history_shows_own_orders_only(): void
    {
        $otherUser = User::factory()->create();

        Order::create([
            'order_number' => 'ORD-MINE',
            'user_id' => $this->user->id,
            'customer_name' => 'My Order',
            'customer_email' => 'me@test.com',
            'customer_phone' => '0912345678',
            'customer_address' => 'My Addr',
            'payment_method' => 'cod',
            'subtotal' => 100000,
            'total' => 100000,
        ]);

        Order::create([
            'order_number' => 'ORD-OTHER',
            'user_id' => $otherUser->id,
            'customer_name' => 'Other Order',
            'customer_email' => 'other@test.com',
            'customer_phone' => '0912345678',
            'customer_address' => 'Other Addr',
            'payment_method' => 'cod',
            'subtotal' => 200000,
            'total' => 200000,
        ]);

        $this->actingAs($this->user)
            ->get('/vi/orders')
            ->assertStatus(200)
            ->assertSee('ORD-MINE')
            ->assertDontSee('ORD-OTHER');
    }
}
