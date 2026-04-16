<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientStockException;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private CartService $cart,
        private OrderService $orderService
    ) {}

    public function index(): View|RedirectResponse
    {
        if ($this->cart->isEmpty()) {
            return redirect('/'.app()->getLocale().'/cart')
                ->with('error', __('Your cart is empty'));
        }

        return view('checkout.index', [
            'items' => $this->cart->items(),
            'total' => $this->cart->total(),
        ]);
    }

    public function process(Request $request): RedirectResponse
    {
        if ($this->cart->isEmpty()) {
            return redirect('/'.app()->getLocale().'/cart')
                ->with('error', __('Your cart is empty'));
        }

        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_address' => 'required|string|max:1000',
            'customer_notes' => 'nullable|string|max:1000',
            'payment_method' => 'required|in:cod,bank_transfer,vnpay,paypal',
        ]);

        $customer = [
            'name' => $validated['customer_name'],
            'email' => $validated['customer_email'],
            'phone' => $validated['customer_phone'],
            'address' => $validated['customer_address'],
            'notes' => $validated['customer_notes'] ?? null,
        ];

        try {
            $order = $this->orderService->createOrder(
                $customer,
                $validated['payment_method'],
                $this->cart
            );
        } catch (InsufficientStockException $e) {
            return redirect('/'.app()->getLocale().'/cart')
                ->with('error', __('Insufficient stock') . ': ' . $e->getMessage());
        }

        return match ($validated['payment_method']) {
            'cod' => redirect('/'.app()->getLocale().'/order/success/'.$order->id),
            'bank_transfer' => redirect('/'.app()->getLocale().'/order/bank-transfer/'.$order->id),
            'vnpay' => redirect(PaymentService::createVnpayUrl($order)),
            'paypal' => redirect(PaymentService::createPaypalOrder($order)),
        };
    }
}
