<?php

namespace App\Http\Controllers;

use App\Models\Order;
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
}
