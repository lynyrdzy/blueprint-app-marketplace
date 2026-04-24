<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Message;
use App\Models\User;

class ChatService
{
    /**
     * Get all messages for an order.
     *
     * @param Order $order
     * @param int $limit
     * @param int $offset
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getMessages(Order $order, $limit = 50, $offset = 0)
    {
        return $order->messages()
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->offset($offset)
            ->limit($limit)
            ->get();
    }

    /**
     * Send a message in an order conversation.
     *
     * @param Order $order
     * @param User $sender
     * @param string $content
     * @return Message
     */
    public function sendMessage(Order $order, User $sender, string $content)
    {
        $message = Message::create([
            'order_id' => $order->id,
            'sender_id' => $sender->id,
            'content' => $content,
        ]);

        // Trigger notification event
        event(new \App\Events\MessageReceived($message));

        return $message;
    }

    /**
     * Check if user has access to order chat.
     *
     * @param Order $order
     * @param User $user
     * @return bool
     */
    public function userCanAccessChat(Order $order, User $user): bool
    {
        // Admin can chat with anyone
        if ($user->isAdmin()) {
            return true;
        }

        // Customer can only chat on their own orders
        return $order->customer_id === $user->id;
    }
}
