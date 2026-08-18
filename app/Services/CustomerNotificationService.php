<?php

namespace App\Services;

use App\Http\Resources\CustomerNotificationResource;
use App\Models\Customer;
use Illuminate\Notifications\DatabaseNotification;

class CustomerNotificationService
{
    public function paginateFor(Customer $customer, int $page, int $perPage): array
    {
        $paginator = $customer->notifications()
            ->latest()
            ->paginate($perPage, ['*'], 'page', $page);

        $items = collect($paginator->items())
            ->map(fn (DatabaseNotification $notification): array =>
                (new CustomerNotificationResource($notification))->resolve()
            )
            ->values()
            ->all();

        return [
            'data' => $items,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
                'unread_count' => $customer->notifications()->whereNull('read_at')->count(),
            ],
        ];
    }

    public function markRead(Customer $customer, string $notificationId): ?array
    {
        $notification = $customer->notifications()->whereKey($notificationId)->first();

        if (!$notification) {
            return null;
        }

        if (!$notification->read_at) {
            $notification->forceFill(['read_at' => now()])->save();
        }

        return (new CustomerNotificationResource($notification->fresh()))->resolve();
    }
}
