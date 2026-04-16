<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function success(Order $order): View
    {
        $order->load('items');

        return view('orders.success', compact('order'));
    }

    public function bankTransfer(Order $order): View
    {
        $order->load('items');

        return view('orders.bank-transfer', [
            'order' => $order,
            'bankName' => config('payment.bank_name'),
            'bankAccount' => config('payment.bank_account'),
            'bankHolder' => config('payment.bank_holder'),
        ]);
    }

    public function history(string $locale): View
    {
        $orders = Order::forUser(auth()->id())
            ->latest()
            ->paginate(15);

        return view('orders.history', compact('orders'));
    }

    public function detail(string $locale, Order $order): View
    {
        abort_unless($order->user_id === auth()->id(), 403);

        $order->load(['items', 'statusLogs']);

        return view('orders.detail', compact('order'));
    }
}
