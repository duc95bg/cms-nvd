<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;

class PaymentService
{
    // ── VNPay ──

    public static function createVnpayUrl(Order $order): string
    {
        $vnpUrl = config('payment.vnpay.url');
        $tmnCode = config('payment.vnpay.tmn_code');
        $hashSecret = config('payment.vnpay.hash_secret');
        $returnUrl = config('payment.vnpay.return_url');

        $params = [
            'vnp_Version' => '2.1.0',
            'vnp_Command' => 'pay',
            'vnp_TmnCode' => $tmnCode,
            'vnp_Amount' => (int) ($order->total * 100),
            'vnp_CurrCode' => 'VND',
            'vnp_TxnRef' => $order->order_number,
            'vnp_OrderInfo' => "Payment for order {$order->order_number}",
            'vnp_OrderType' => 'other',
            'vnp_Locale' => 'vn',
            'vnp_ReturnUrl' => $returnUrl,
            'vnp_IpAddr' => request()->ip(),
            'vnp_CreateDate' => now()->format('YmdHis'),
        ];

        ksort($params);
        $queryString = http_build_query($params, '', '&');
        $hash = hash_hmac('sha512', $queryString, $hashSecret);

        return $vnpUrl . '?' . $queryString . '&vnp_SecureHash=' . $hash;
    }

    public static function verifyVnpayHash(array $params): bool
    {
        $hashSecret = config('payment.vnpay.hash_secret');

        $secureHash = $params['vnp_SecureHash'] ?? '';
        unset($params['vnp_SecureHash'], $params['vnp_SecureHashType']);

        ksort($params);
        $queryString = http_build_query($params, '', '&');
        $computed = hash_hmac('sha512', $queryString, $hashSecret);

        return hash_equals($computed, $secureHash);
    }

    // ── PayPal ──

    public static function createPaypalOrder(Order $order): string
    {
        $accessToken = self::getPaypalAccessToken();
        $baseUrl = self::paypalBaseUrl();
        $usdRate = config('payment.paypal.usd_rate', 25000);
        $usdTotal = number_format(round($order->total / $usdRate, 2), 2, '.', '');

        $response = Http::withToken($accessToken)
            ->post("{$baseUrl}/v2/checkout/orders", [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'reference_id' => $order->order_number,
                        'amount' => [
                            'currency_code' => 'USD',
                            'value' => $usdTotal,
                        ],
                        'description' => "Order {$order->order_number}",
                    ],
                ],
                'application_context' => [
                    'return_url' => url('/payment/paypal/return?order=' . $order->id),
                    'cancel_url' => url('/payment/paypal/cancel?order=' . $order->id),
                    'brand_name' => config('app.name'),
                    'user_action' => 'PAY_NOW',
                ],
            ]);

        $data = $response->json();

        // Find approval URL
        $approveLink = collect($data['links'] ?? [])->firstWhere('rel', 'approve');

        if (!$approveLink) {
            throw new \RuntimeException('PayPal order creation failed: ' . json_encode($data));
        }

        // Store PayPal order ID for later capture
        $order->update(['payment_data' => ['paypal_order_id' => $data['id']]]);

        return $approveLink['href'];
    }

    public static function capturePaypalPayment(string $paypalOrderId): array
    {
        $accessToken = self::getPaypalAccessToken();
        $baseUrl = self::paypalBaseUrl();

        $response = Http::withToken($accessToken)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post("{$baseUrl}/v2/checkout/orders/{$paypalOrderId}/capture");

        return $response->json();
    }

    private static function getPaypalAccessToken(): string
    {
        $baseUrl = self::paypalBaseUrl();
        $clientId = config('payment.paypal.client_id');
        $secret = config('payment.paypal.secret');

        $response = Http::withBasicAuth($clientId, $secret)
            ->asForm()
            ->post("{$baseUrl}/v1/oauth2/token", [
                'grant_type' => 'client_credentials',
            ]);

        return $response->json('access_token');
    }

    private static function paypalBaseUrl(): string
    {
        return config('payment.paypal.mode') === 'sandbox'
            ? 'https://api-m.sandbox.paypal.com'
            : 'https://api-m.paypal.com';
    }
}
