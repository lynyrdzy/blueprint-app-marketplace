<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Exceptions\InsufficientStockException;
use Illuminate\Support\Facades\DB;
use App\Events\OrderCreated;

class OrderService
{
    /**
     * Create a new order with items (transactional).
     *
     * @param \App\Models\User $customer
     * @param array $items [['product_id' => 1, 'quantity' => 2], ...]
     * @param array $delivery ['name', 'phone', 'address']
     * @return \App\Models\Order
     * @throws InsufficientStockException
     */
    public function createOrder($customer, array $items, array $delivery)
    {
        return DB::transaction(function () use ($customer, $items, $delivery) {
            // Step 1: Validate stock availability for all items
            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);
                
                if (!$product->hasStock($item['quantity'])) {
                    throw new InsufficientStockException(
                        "Product '{$product->name}' has insufficient stock"
                    );
                }
            }

            // Step 2: Calculate totals
            $subtotal = 0;
            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $subtotal += $product->price * $item['quantity'];
            }

            // Step 3: Create order
            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'customer_id' => $customer->id,
                'customer_name' => $delivery['name'],
                'customer_phone' => $delivery['phone'],
                'customer_address' => $delivery['address'],
                'subtotal' => $subtotal,
                'shipping_cost' => 0,
                'total' => $subtotal,
                'status' => Order::STATUS_PENDING,
            ]);

            // Step 4: Create order items and decrement stock
            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->price,
                    'subtotal' => $product->price * $item['quantity'],
                ]);

                $product->decrementStock($item['quantity']);
            }

            // Step 5: Trigger event for notifications
            event(new OrderCreated($order));

            return $order;
        });
    }

    /**
     * Update order status with validation.
     *
     * @param Order $order
     * @param string $newStatus
     * @param string|null $adminNotes
     * @return Order
     */
    public function updateStatus(Order $order, string $newStatus, ?string $adminNotes = null)
    {
        if (!$order->canTransitionTo($newStatus)) {
            throw new \InvalidArgumentException(
                "Cannot transition from {$order->status} to {$newStatus}"
            );
        }

        $oldStatus = $order->status;
        
        $order->update([
            'status' => $newStatus,
            'admin_notes' => $adminNotes ?? $order->admin_notes,
        ]);

        // Trigger event
        event(new \App\Events\OrderStatusChanged($order, $oldStatus, $newStatus));

        return $order;
    }

    /**
     * Cancel order and restore stock.
     *
     * @param Order $order
     * @return void
     */
    public function cancelOrder(Order $order)
    {
        if ($order->status === Order::STATUS_COMPLETED) {
            throw new \InvalidArgumentException('Cannot cancel a completed order');
        }

        DB::transaction(function () use ($order) {
            // Restore stock
            foreach ($order->items as $item) {
                $item->product->incrementStock($item->quantity);
            }

            $order->update(['status' => Order::STATUS_CANCELLED]);
        });
    }

    /**
     * Generate unique order number.
     *
     * @return string
     */
    private function generateOrderNumber(): string
    {
        $date = now()->format('Ymd');
        $count = Order::whereDate('created_at', now()->toDateString())->count() + 1;
        
        return 'ORD-' . $date . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }
}
