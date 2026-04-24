<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Requests\Order\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use App\Exceptions\InsufficientStockException;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Create a new order (customer).
     *
     * POST /api/orders
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        try {
            $order = $this->orderService->createOrder(
                $request->user(),
                $request->validated()['items'],
                [
                    'name' => $request->validated()['customer_name'],
                    'phone' => $request->validated()['customer_phone'],
                    'address' => $request->validated()['customer_address'],
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => new OrderResource($order->load('items')),
            ], 201);

        } catch (InsufficientStockException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get customer's orders.
     *
     * GET /api/orders
     */
    public function index(): JsonResponse
    {
        $orders = Order::where('customer_id', auth()->id())
            ->with('items')
            ->recent()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => OrderResource::collection($orders),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'total' => $orders->total(),
                'per_page' => $orders->perPage(),
            ],
        ]);
    }

    /**
     * Get order details.
     *
     * GET /api/orders/{id}
     */
    public function show(Order $order): JsonResponse
    {
        // Authorize: customer can view own orders, admin can view all
        $this->authorize('view', $order);

        return response()->json([
            'success' => true,
            'data' => new OrderResource($order->load('items', 'messages.sender')),
        ]);
    }

    /**
     * Update order status (admin only).
     *
     * PATCH /api/orders/{id}/status
     */
    public function updateStatus(Order $order, UpdateOrderStatusRequest $request): JsonResponse
    {
        $this->authorize('update', $order);

        $newStatus = $request->validated()['status'];
        $adminNotes = $request->validated()['admin_notes'] ?? null;

        try {
            $order = $this->orderService->updateStatus($order, $newStatus, $adminNotes);

            return response()->json([
                'success' => true,
                'message' => 'Order status updated',
                'data' => new OrderResource($order),
            ]);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * List all orders (admin only).
     *
     * GET /api/admin/orders
     */
    public function listAll(): JsonResponse
    {
        $this->authorize('viewAny', Order::class);

        $status = request('status');
        $query = Order::with('items', 'customer');

        if ($status) {
            $query->where('status', $status);
        }

        $orders = $query->recent()->paginate(15);

        $stats = [
            'total' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'confirmed' => Order::where('status', 'confirmed')->count(),
            'processing' => Order::where('status', 'processing')->count(),
            'shipped' => Order::where('status', 'shipped')->count(),
            'completed' => Order::where('status', 'completed')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => OrderResource::collection($orders),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'total' => $orders->total(),
                'per_page' => $orders->perPage(),
                'stats' => $stats,
            ],
        ]);
    }
}
