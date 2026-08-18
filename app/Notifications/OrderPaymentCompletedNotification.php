<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class OrderPaymentCompletedNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return $this->payload();
    }

    public function __construct(private readonly Order $order)
    {
        $this->id = (string) Str::uuid();
    }

    public $id;

    public function payload(): array
    {
        return [
            'type' => 'order_payment_completed',
            'title' => 'Thanh toán thành công',
            'message' => "Đơn hàng {$this->order->code} đã được thanh toán thành công.",
            'order' => [
                'id' => $this->order->id,
                'code' => $this->order->code,
                'amount' => (int) $this->order->amount,
                'status' => 'completed',
                'payment_time' => $this->order->payment_time?->toISOString(),
            ],
        ];
    }
}
