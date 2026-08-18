<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderPaymentCompletedBroadcast implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly int $customerId,
        public readonly array $notification
    ) {
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel("customers.{$this->customerId}")];
    }

    public function broadcastAs(): string
    {
        return 'order.payment.completed';
    }

    public function broadcastWith(): array
    {
        return $this->notification;
    }
}
