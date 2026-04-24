<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Models\Notification;

class NotificationService
{
    /**
     * Notify admin of new order.
     *
     * @param Order $order
     * @return void
     */
    public function notifyAdminNewOrder(Order $order)
    {
        $admin = User::where('role', 'admin')->first();

        if (!$admin) {
            return;
        }

        Notification::create([
            'user_id' => $admin->id,
            'order_id' => $order->id,
            'type' => 'order_created',
            'title' => 'New Order Received',
            'content' => "Order {$order->order_number} from {$order->customer_name}",
        ]);

        // Optional: Send WhatsApp notification (via Twilio)
        // $this->sendWhatsAppNotification($admin, $order);
    }

    /**
     * Notify customer of status change.
     *
     * @param Order $order
     * @param string $oldStatus
     * @param string $newStatus
     * @return void
     */
    public function notifyCustomerStatusChange(Order $order, string $oldStatus, string $newStatus)
    {
        Notification::create([
            'user_id' => $order->customer_id,
            'order_id' => $order->id,
            'type' => 'status_changed',
            'title' => 'Order Status Updated',
            'content' => "Your order {$order->order_number} is now {$newStatus}",
        ]);

        // Optional: Send email notification
        // Mail::to($order->customer->email)->send(new OrderStatusChanged($order, $newStatus));
    }

    /**
     * Mark notification as read.
     *
     * @param Notification $notification
     * @return void
     */
    public function markAsRead(Notification $notification)
    {
        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }
}
