<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\ListCustomerNotificationsRequest;
use App\Services\CustomerNotificationService;

class CustomerNotificationController extends Controller
{
    public function __construct(private readonly CustomerNotificationService $notificationService)
    {
    }

    public function index(ListCustomerNotificationsRequest $request)
    {
        $customer = auth('customer')->user();

        return $this->successResponse($this->notificationService->paginateFor(
            $customer,
            (int) $request->input('page', 1),
            (int) $request->input('per_page', 10)
        ));
    }

    public function markRead(string $notificationId)
    {
        $notification = $this->notificationService->markRead(
            auth('customer')->user(),
            $notificationId
        );

        if (!$notification) {
            return $this->notFoundErrorResponse();
        }

        return $this->successResponse($notification);
    }
}
