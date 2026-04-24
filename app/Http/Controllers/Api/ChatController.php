<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\StoreMessageRequest;
use App\Http\Resources\MessageResource;
use App\Models\Order;
use App\Services\ChatService;

class ChatController extends Controller
{
    protected $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    /**
     * Get messages for an order.
     *
     * GET /api/orders/{order_id}/messages
     */
    public function index(Order $order)
    {
        $this->authorize('chat', $order);

        $messages = $this->chatService->getMessages(
            $order,
            limit: request('limit', 50),
            offset: request('offset', 0)
        );

        return response()->json([
            'success' => true,
            'data' => MessageResource::collection($messages),
        ]);
    }

    /**
     * Send a message.
     *
     * POST /api/orders/{order_id}/messages
     */
    public function store(Order $order, StoreMessageRequest $request)
    {
        $this->authorize('chat', $order);

        $message = $this->chatService->sendMessage(
            $order,
            auth()->user(),
            $request->validated()['content']
        );

        return response()->json([
            'success' => true,
            'message' => 'Message sent',
            'data' => new MessageResource($message->load('sender')),
        ], 201);
    }
}
