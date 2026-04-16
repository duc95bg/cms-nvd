<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Create an order from the current cart contents.
     *
     * @param  array  $customer  Keys: name, email, phone, address, notes
     * @param  string $paymentMethod  One of: cod, bank_transfer, vnpay, paypal
     * @param  CartService $cart
     * @return Order  The created order with items loaded
     *
     * @throws InsufficientStockException if stock changed between cart and checkout
     */
    public function createOrder(array $customer, string $paymentMethod, CartService $cart): Order
    {
        return DB::transaction(function () use ($customer, $paymentMethod, $cart) {
            $items = $cart->items();
            $subtotal = $items->sum('line_total');

            // Lock and validate stock for each item
            foreach ($items as $item) {
                if ($item->variant_id) {
                    $variant = ProductVariant::lockForUpdate()->find($item->variant_id);

                    if (!$variant || $variant->stock < $item->quantity) {
                        throw new InsufficientStockException(
                            $item->product->t('name'),
                            $item->quantity,
                            $variant?->stock ?? 0
                        );
                    }

                    $variant->decrement('stock', $item->quantity);
                }
            }

            // Create order
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => auth()->id(),
                'customer_name' => $customer['name'],
                'customer_email' => $customer['email'],
                'customer_phone' => $customer['phone'],
                'customer_address' => $customer['address'],
                'customer_notes' => $customer['notes'] ?? null,
                'payment_method' => $paymentMethod,
                'payment_status' => 'pending',
                'status' => 'pending',
                'subtotal' => $subtotal,
                'total' => $subtotal,
            ]);

            // Create order items with snapshots
            foreach ($items as $item) {
                $variantInfo = null;
                if ($item->variant) {
                    $variantInfo = $item->variant->attributeValues->map(fn ($av) => [
                        'attribute' => $av->attribute->t('name'),
                        'value' => $av->t('value'),
                    ])->all();
                }

                $order->items()->create([
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id,
                    'product_name' => $item->product->t('name'),
                    'product_price' => $item->unit_price,
                    'variant_info' => $variantInfo,
                    'quantity' => $item->quantity,
                    'line_total' => $item->line_total,
                ]);
            }

            // Initial status log
            OrderStatusLog::create([
                'order_id' => $order->id,
                'old_status' => '',
                'new_status' => 'pending',
                'note' => 'Order created',
                'changed_by' => auth()->id(),
            ]);

            // Clear cart
            $cart->clear();

            return $order->load('items');
        });
    }
}
