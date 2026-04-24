<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Services\NotificationService;

class SendOrderNotification
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function handle(OrderCreated $event)
    {
        $this->notificationService->notifyAdminNewOrder($event->order);
    }
}
