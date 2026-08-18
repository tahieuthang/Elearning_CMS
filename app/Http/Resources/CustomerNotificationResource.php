<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerNotificationResource extends JsonResource
{
    public function toArray($request): array
    {
        $payload = is_array($this->data) ? $this->data : [];
        $order = is_array($payload['order'] ?? null) ? $payload['order'] : null;

        return [
            'id' => (string) $this->id,
            'type' => (string) ($payload['type'] ?? 'general'),
            'title' => (string) ($payload['title'] ?? 'Thông báo'),
            'message' => (string) ($payload['message'] ?? ''),
            'order' => $order,
            'read_at' => $this->read_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
