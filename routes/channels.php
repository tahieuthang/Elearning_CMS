<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('customers.{customerId}', function ($customer, int $customerId): bool {
    return (int) $customer->id === $customerId;
}, ['guards' => ['customer']]);
