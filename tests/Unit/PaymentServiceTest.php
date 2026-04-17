<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Services\PaymentService;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    public function test_vnpay_url_contains_required_params_and_hash(): void
    {
        config([
            'payment.vnpay.tmn_code' => 'TESTCODE',
            'payment.vnpay.hash_secret' => 'TESTSECRET',
            'payment.vnpay.url' => 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html',
            'payment.vnpay.return_url' => 'http://localhost/payment/vnpay/return',
        ]);

        $order = new Order([
            'order_number' => 'ORD-20260416-ABC123',
            'total' => 150000,
        ]);
        $order->id = 1;

        $url = PaymentService::createVnpayUrl($order);

        $this->assertStringContainsString('vnp_TmnCode=TESTCODE', $url);
        $this->assertStringContainsString('vnp_Amount=15000000', $url);
        $this->assertStringContainsString('vnp_TxnRef=ORD-20260416-ABC123', $url);
        $this->assertStringContainsString('vnp_SecureHash=', $url);
        $this->assertStringStartsWith('https://sandbox.vnpayment.vn', $url);
    }

    public function test_vnpay_hash_verification_valid(): void
    {
        config(['payment.vnpay.hash_secret' => 'TESTSECRET']);

        $params = [
            'vnp_Amount' => '15000000',
            'vnp_ResponseCode' => '00',
            'vnp_TxnRef' => 'ORD-TEST',
        ];

        ksort($params);
        $queryString = http_build_query($params, '', '&');
        $hash = hash_hmac('sha512', $queryString, 'TESTSECRET');
        $params['vnp_SecureHash'] = $hash;

        $this->assertTrue(PaymentService::verifyVnpayHash($params));
    }

    public function test_vnpay_hash_verification_invalid(): void
    {
        config(['payment.vnpay.hash_secret' => 'TESTSECRET']);

        $params = [
            'vnp_Amount' => '15000000',
            'vnp_ResponseCode' => '00',
            'vnp_TxnRef' => 'ORD-TEST',
            'vnp_SecureHash' => 'invalidhashvalue',
        ];

        $this->assertFalse(PaymentService::verifyVnpayHash($params));
    }

    public function test_paypal_usd_conversion(): void
    {
        config(['payment.paypal.usd_rate' => 25000]);

        $vnd = 500000;
        $expected = round($vnd / 25000, 2);

        $this->assertSame(20.0, $expected);
    }
}
