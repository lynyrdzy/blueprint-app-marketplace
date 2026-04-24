<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Order;

class OrderPolicy
{
    public function view(User $user, Order $order)
    {
        // Admin can view all orders, customer can only view their own
        return $user->isAdmin() || $user->id === $order->customer_id;
    }

    public function update(User $user, Order $order)
    {
        return $user->isAdmin();
    }

    public function viewAny(User $user)
    {
        return $user->isAdmin();
    }

    public function chat(User $user, Order $order)
    {
        return $user->isAdmin() || $user->id === $order->customer_id;
    }
}
