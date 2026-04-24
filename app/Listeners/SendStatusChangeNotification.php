<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Services\NotificationService;

class SendStatusChangeNotification
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function handle(OrderStatusChanged $event)
    {
        $this->notificationService->notifyCustomerStatusChange(
            $event->order,
            $event->oldStatus,
            $event->newStatus
        );
    }
}
