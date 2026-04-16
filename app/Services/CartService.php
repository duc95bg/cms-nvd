<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;

class CartService
{
    private const SESSION_KEY = 'cart';

    public function add(int $productId, ?int $variantId, int $qty = 1): void
    {
        $key = $this->makeKey($productId, $variantId);
        $cart = $this->getCart();

        $existingQty = isset($cart[$key]) ? $cart[$key]['quantity'] : 0;
        $totalQty = $existingQty + $qty;

        $this->validateStock($productId, $variantId, $totalQty);

        $cart[$key] = [
            'product_id' => $productId,
            'variant_id' => $variantId,
            'quantity' => $totalQty,
        ];

        $this->saveCart($cart);
    }

    public function update(string $key, int $qty): void
    {
        $cart = $this->getCart();

        if (!isset($cart[$key])) {
            return;
        }

        if ($qty <= 0) {
            $this->remove($key);
            return;
        }

        $item = $cart[$key];
        $this->validateStock($item['product_id'], $item['variant_id'], $qty);

        $cart[$key]['quantity'] = $qty;
        $this->saveCart($cart);
    }

    public function remove(string $key): void
    {
        $cart = $this->getCart();
        unset($cart[$key]);
        $this->saveCart($cart);
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    public function items(): Collection
    {
        $cart = $this->getCart();

        if (empty($cart)) {
            return collect();
        }

        $productIds = array_unique(array_column($cart, 'product_id'));
        $variantIds = array_filter(array_unique(array_column($cart, 'variant_id')));

        $products = Product::with('images')->whereIn('id', $productIds)->get()->keyBy('id');
        $variants = ProductVariant::with('attributeValues')->whereIn('id', $variantIds)->get()->keyBy('id');

        return collect($cart)->map(function ($item, $key) use ($products, $variants) {
            $product = $products->get($item['product_id']);
            $variant = $item['variant_id'] ? $variants->get($item['variant_id']) : null;

            if (!$product) {
                return null;
            }

            $unitPrice = ($variant && $variant->price !== null)
                ? (float) $variant->price
                : (float) $product->base_price;

            return (object) [
                'key' => $key,
                'product_id' => $item['product_id'],
                'variant_id' => $item['variant_id'],
                'quantity' => $item['quantity'],
                'product' => $product,
                'variant' => $variant,
                'unit_price' => $unitPrice,
                'line_total' => $unitPrice * $item['quantity'],
            ];
        })->filter()->values();
    }

    public function total(): float
    {
        return $this->items()->sum('line_total');
    }

    public function count(): int
    {
        return array_sum(array_column($this->getCart(), 'quantity'));
    }

    public function isEmpty(): bool
    {
        return empty($this->getCart());
    }

    private function makeKey(int $productId, ?int $variantId): string
    {
        return $productId . '-' . ($variantId ?? '0');
    }

    private function getCart(): array
    {
        return session(self::SESSION_KEY, []);
    }

    private function saveCart(array $cart): void
    {
        session([self::SESSION_KEY => $cart]);
    }

    private function validateStock(int $productId, ?int $variantId, int $requiredQty): void
    {
        if ($variantId) {
            $variant = ProductVariant::find($variantId);
            if (!$variant || $variant->stock < $requiredQty) {
                $product = Product::find($productId);
                throw new InsufficientStockException(
                    $product?->t('name') ?? 'Unknown',
                    $requiredQty,
                    $variant?->stock ?? 0
                );
            }
        }
    }
}
