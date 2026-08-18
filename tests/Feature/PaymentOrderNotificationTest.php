<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PaymentOrderNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['constants.vnpay_payment_hashsecret' => 'test-vnpay-secret']);
    }

    public function test_successful_ipn_completes_order_and_creates_one_notification(): void
    {
        $customer = $this->createCustomer();
        $order = $this->createProcessingOrder($customer);

        $response = $this->getJson('/api/payment/response?'.http_build_query(
            $this->signedIpn($order, ['vnp_ResponseCode' => '00'])
        ));

        $response->assertOk()->assertJson([
            'RspCode' => '00',
            'Message' => 'Confirm Success',
        ]);
        $this->assertSame(config('constants.order_status.completed'), $order->fresh()->status);
        $this->assertDatabaseHas('payment_transactions', [
            'order_id' => $order->id,
            'status' => config('constants.payment_transaction_status.completed'),
        ]);
        $this->assertCount(1, $customer->notifications()->get());
    }

    public function test_repeated_successful_ipn_does_not_create_duplicate_notification(): void
    {
        $customer = $this->createCustomer();
        $order = $this->createProcessingOrder($customer);
        $payload = $this->signedIpn($order, ['vnp_ResponseCode' => '00']);

        $this->getJson('/api/payment/response?'.http_build_query($payload))->assertOk();
        $this->getJson('/api/payment/response?'.http_build_query($payload))
            ->assertJson(['RspCode' => '02']);

        $this->assertCount(1, $customer->notifications()->get());
    }

    public function test_failed_ipn_cancels_order_without_creating_notification(): void
    {
        $customer = $this->createCustomer();
        $order = $this->createProcessingOrder($customer);

        $this->getJson('/api/payment/response?'.http_build_query(
            $this->signedIpn($order, ['vnp_ResponseCode' => '24', 'vnp_TransactionStatus' => '24'])
        ))->assertJson(['RspCode' => '00']);

        $this->assertSame(config('constants.order_status.cancelled'), $order->fresh()->status);
        $this->assertCount(0, $customer->notifications()->get());
    }

    public function test_invalid_signature_does_not_change_order_or_create_notification(): void
    {
        $customer = $this->createCustomer();
        $order = $this->createProcessingOrder($customer);
        $payload = $this->signedIpn($order, ['vnp_ResponseCode' => '00']);
        $payload['vnp_SecureHash'] = str_repeat('0', 128);

        $this->getJson('/api/payment/response?'.http_build_query($payload))
            ->assertJson(['RspCode' => '97']);

        $this->assertSame(config('constants.order_status.processing'), $order->fresh()->status);
        $this->assertCount(0, $customer->notifications()->get());
    }

    private function createCustomer(): Customer
    {
        return Customer::create([
            'first_name' => 'Payment',
            'last_name' => 'Customer',
            'email' => uniqid('payment-', true).'@example.test',
            'password' => Hash::make('password'),
            'status' => config('constants.customer_status_enable'),
        ]);
    }

    private function createProcessingOrder(Customer $customer): Order
    {
        $order = Order::create([
            'code' => 'OD-TEST-'.uniqid(),
            'amount' => 299000,
            'customer_id' => $customer->id,
            'payment_method' => 'vnpay',
            'status' => config('constants.order_status.processing'),
        ]);

        PaymentTransaction::create([
            'code' => 'PT-TEST-'.uniqid(),
            'order_id' => $order->id,
            'customer_id' => $customer->id,
            'amount' => $order->amount,
            'payment_method' => 'vnpay',
            'status' => config('constants.payment_transaction_status.waiting_confirm'),
        ]);

        return $order;
    }

    private function signedIpn(Order $order, array $overrides = []): array
    {
        $payload = array_merge([
            'vnp_Amount' => $order->amount * 100,
            'vnp_Command' => 'pay',
            'vnp_CreateDate' => '20260818143000',
            'vnp_ResponseCode' => '00',
            'vnp_TmnCode' => 'TESTCODE',
            'vnp_TransactionNo' => '123456',
            'vnp_TransactionStatus' => '00',
            'vnp_TxnRef' => $order->code,
            'vnp_Version' => '2.1.0',
        ], $overrides);

        ksort($payload);
        $hashData = http_build_query($payload, '', '&', PHP_QUERY_RFC3986);
        $payload['vnp_SecureHash'] = hash_hmac(
            'sha512',
            $hashData,
            'test-vnpay-secret'
        );

        return $payload;
    }
}
