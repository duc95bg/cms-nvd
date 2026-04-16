<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    // ── VNPay ──

    public function vnpayReturn(Request $request): RedirectResponse
    {
        $params = $request->all();
        $locale = app()->getLocale();

        if (!PaymentService::verifyVnpayHash($params)) {
            return redirect("/{$locale}/products")
                ->with('error', __('Payment verification failed'));
        }

        $orderNumber = $params['vnp_TxnRef'] ?? '';
        $order = Order::where('order_number', $orderNumber)->first();

        if (!$order) {
            return redirect("/{$locale}/products")
                ->with('error', __('Order not found'));
        }

        $responseCode = $params['vnp_ResponseCode'] ?? '99';

        if ($responseCode === '00') {
            $order->update([
                'payment_status' => 'paid',
                'status' => 'confirmed',
                'payment_data' => $params,
            ]);

            return redirect("/{$locale}/order/success/{$order->id}");
        }

        $order->update([
            'payment_status' => 'failed',
            'payment_data' => $params,
        ]);

        return redirect("/{$locale}/cart")
            ->with('error', __('Payment failed. Please try again.'));
    }

    public function vnpayIpn(Request $request): JsonResponse
    {
        $params = $request->all();

        if (!PaymentService::verifyVnpayHash($params)) {
            return response()->json(['RspCode' => '97', 'Message' => 'Invalid hash']);
        }

        $orderNumber = $params['vnp_TxnRef'] ?? '';
        $order = Order::where('order_number', $orderNumber)->first();

        if (!$order) {
            return response()->json(['RspCode' => '01', 'Message' => 'Order not found']);
        }

        if ($order->payment_status === 'paid') {
            return response()->json(['RspCode' => '02', 'Message' => 'Already confirmed']);
        }

        $responseCode = $params['vnp_ResponseCode'] ?? '99';

        if ($responseCode === '00') {
            $order->update([
                'payment_status' => 'paid',
                'status' => 'confirmed',
                'payment_data' => $params,
            ]);
        } else {
            $order->update([
                'payment_status' => 'failed',
                'payment_data' => $params,
            ]);
        }

        return response()->json(['RspCode' => '00', 'Message' => 'Confirmed']);
    }

    // ── PayPal ──

    public function paypalReturn(Request $request): RedirectResponse
    {
        $locale = app()->getLocale();
        $orderId = $request->query('order');
        $token = $request->query('token');

        $order = Order::find($orderId);

        if (!$order || !$token) {
            return redirect("/{$locale}/products")
                ->with('error', __('Payment verification failed'));
        }

        try {
            $captureData = PaymentService::capturePaypalPayment($token);
            $status = $captureData['status'] ?? '';

            if ($status === 'COMPLETED') {
                $order->update([
                    'payment_status' => 'paid',
                    'status' => 'confirmed',
                    'payment_data' => $captureData,
                ]);

                return redirect("/{$locale}/order/success/{$order->id}");
            }

            $order->update([
                'payment_status' => 'failed',
                'payment_data' => $captureData,
            ]);

            return redirect("/{$locale}/cart")
                ->with('error', __('Payment failed. Please try again.'));
        } catch (\Exception $e) {
            $order->update(['payment_status' => 'failed']);

            return redirect("/{$locale}/cart")
                ->with('error', __('Payment processing error'));
        }
    }

    public function paypalCancel(Request $request): RedirectResponse
    {
        $locale = app()->getLocale();
        $orderId = $request->query('order');
        $order = Order::find($orderId);

        if ($order) {
            $order->update(['payment_status' => 'failed']);
        }

        return redirect("/{$locale}/cart")
            ->with('error', __('Payment was cancelled'));
    }
}
