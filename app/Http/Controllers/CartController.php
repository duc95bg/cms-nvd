<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientStockException;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private CartService $cart) {}

    public function add(Request $request): RedirectResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|integer',
            'qty' => 'required|integer|min:1',
        ]);

        try {
            $this->cart->add(
                (int) $request->product_id,
                $request->variant_id ? (int) $request->variant_id : null,
                (int) $request->qty
            );

            return back()->with('success', __('Added to cart'));
        } catch (InsufficientStockException $e) {
            return back()->with('error', __('Insufficient stock') . ': ' . $e->availableStock . ' ' . __('available'));
        }
    }

    public function index(): View
    {
        return view('cart.index', [
            'items' => $this->cart->items(),
            'total' => $this->cart->total(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'key' => 'required|string',
            'qty' => 'required|integer|min:0',
        ]);

        try {
            $this->cart->update($request->key, (int) $request->qty);
            return back()->with('success', __('Cart updated'));
        } catch (InsufficientStockException $e) {
            return back()->with('error', __('Insufficient stock') . ': ' . $e->availableStock . ' ' . __('available'));
        }
    }

    public function remove(Request $request): RedirectResponse
    {
        $request->validate([
            'key' => 'required|string',
        ]);

        $this->cart->remove($request->key);

        return back()->with('success', __('Item removed'));
    }
}
