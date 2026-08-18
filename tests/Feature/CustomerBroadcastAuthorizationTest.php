<?php

namespace Tests\Feature;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class CustomerBroadcastAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'broadcasting.default' => 'reverb',
            'broadcasting.connections.reverb.key' => 'test-key',
            'broadcasting.connections.reverb.secret' => 'test-secret',
            'broadcasting.connections.reverb.app_id' => 'test-app',
        ]);
    }

    public function test_customer_can_authorize_its_private_channel(): void
    {
        $customer = $this->createCustomer();

        $this->asCustomer($customer)
            ->postJson('/api/broadcasting/auth', [
                'socket_id' => '123.456',
                'channel_name' => "private-customers.{$customer->id}",
            ])
            ->assertStatus(200);
    }

    public function test_customer_cannot_authorize_another_customers_private_channel(): void
    {
        $customer = $this->createCustomer();

        $this->asCustomer($customer)
            ->postJson('/api/broadcasting/auth', [
                'socket_id' => '123.456',
                'channel_name' => 'private-customers.999999',
            ])
            ->assertForbidden();
    }

    public function test_broadcast_authorization_requires_customer_authentication(): void
    {
        $this->postJson('/api/broadcasting/auth', [
            'socket_id' => '123.456',
            'channel_name' => 'private-customers.1',
        ])->assertUnauthorized();
    }

    private function createCustomer(): Customer
    {
        return Customer::create([
            'first_name' => 'Broadcast',
            'last_name' => 'Customer',
            'email' => uniqid('broadcast-', true).'@example.test',
            'password' => Hash::make('password'),
            'status' => config('constants.customer_status_enable'),
        ]);
    }

    private function asCustomer(Customer $customer): static
    {
        return $this->withHeader('Authorization', 'Bearer '.JWTAuth::fromUser($customer));
    }
}
