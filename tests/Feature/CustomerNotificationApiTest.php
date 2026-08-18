<?php

namespace Tests\Feature;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class CustomerNotificationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_list_only_its_notifications_with_unread_metadata(): void
    {
        $customer = $this->createCustomer('owner@example.test');
        $otherCustomer = $this->createCustomer('other@example.test');

        $firstNotification = $customer->notifications()->create($this->notificationData('first', null));
        $readNotification = $customer->notifications()->create($this->notificationData('read', now()));
        $readNotification->forceFill(['created_at' => now()->addSecond()])->save();
        $otherCustomer->notifications()->create($this->notificationData('other', null));

        $response = $this->asCustomer($customer)->getJson('/api/customer/notifications?per_page=10&page=1');

        $response
            ->assertOk()
            ->assertJsonPath('data.data.0.id', (string) $readNotification->id)
            ->assertJsonPath('data.meta.total', 2)
            ->assertJsonPath('data.meta.unread_count', 1)
            ->assertJsonCount(2, 'data.data')
            ->assertJsonPath('data.data.1.id', (string) $firstNotification->id);

        $this->assertStringNotContainsString('other@example.test', $response->getContent());
    }

    public function test_customer_can_mark_own_notification_read_idempotently(): void
    {
        $customer = $this->createCustomer('owner@example.test');
        $notification = $customer->notifications()->create($this->notificationData('first', null));

        $firstResponse = $this->asCustomer($customer)
            ->patchJson("/api/customer/notifications/{$notification->id}/read");
        $firstReadAt = $firstResponse->json('data.read_at');

        $secondResponse = $this->asCustomer($customer)
            ->patchJson("/api/customer/notifications/{$notification->id}/read");

        $firstResponse->assertOk()->assertJsonPath('data.id', (string) $notification->id);
        $secondResponse->assertOk()->assertJsonPath('data.read_at', $firstReadAt);
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_customer_cannot_mark_another_customers_notification_read(): void
    {
        $customer = $this->createCustomer('owner@example.test');
        $otherCustomer = $this->createCustomer('other@example.test');
        $notification = $otherCustomer->notifications()->create($this->notificationData('other', null));

        $this->asCustomer($customer)
            ->patchJson("/api/customer/notifications/{$notification->id}/read")
            ->assertNotFound();

        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_notification_list_validates_page_size(): void
    {
        $customer = $this->createCustomer('owner@example.test');

        $this->asCustomer($customer)
            ->getJson('/api/customer/notifications?per_page=51')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('per_page');
    }

    public function test_notification_endpoints_require_customer_authentication(): void
    {
        $this->getJson('/api/customer/notifications')->assertUnauthorized();
        $this->patchJson('/api/customer/notifications/00000000-0000-0000-0000-000000000000/read')
            ->assertUnauthorized();
    }

    private function createCustomer(string $email): Customer
    {
        return Customer::create([
            'first_name' => 'Test',
            'last_name' => 'Customer',
            'email' => $email,
            'password' => Hash::make('password'),
            'status' => config('constants.customer_status_enable'),
        ]);
    }

    private function asCustomer(Customer $customer): static
    {
        return $this->withHeader('Authorization', 'Bearer '.JWTAuth::fromUser($customer));
    }

    private function notificationData(string $code, $readAt): array
    {
        return [
            'id' => (string) Str::uuid(),
            'type' => 'order_payment_completed',
            'data' => json_encode([
                'type' => 'order_payment_completed',
                'title' => 'Thanh toán thành công',
                'message' => "Đơn hàng {$code} đã được thanh toán thành công.",
                'order' => [
                    'id' => 1,
                    'code' => "OD-{$code}",
                    'amount' => 100000,
                    'status' => 'completed',
                    'payment_time' => now()->toISOString(),
                ],
            ], JSON_THROW_ON_ERROR),
            'read_at' => $readAt,
        ];
    }
}
