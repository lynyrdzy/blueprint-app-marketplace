@extends('layouts.app')

@section('title', 'Order Tracking - ' . $order->order_number)

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold mb-2">{{ $order->order_number }}</h1>
    <p class="text-gray-600">Placed on {{ $order->created_at->format('d M Y H:i') }}</p>
</div>

<!-- Status Timeline -->
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h2 class="text-lg font-semibold mb-4">Order Status</h2>
    
    <div class="space-y-4">
        @php
            $statuses = ['pending', 'confirmed', 'processing', 'shipped', 'completed'];
            $currentStatusIndex = array_search($order->status, $statuses);
        @endphp

        @foreach($statuses as $index => $status)
            <div class="flex items-center">
                <div class="w-8 h-8 rounded-full flex items-center justify-center mr-4 text-white font-bold
                            {{ $index <= $currentStatusIndex ? 'bg-green-600' : 'bg-gray-300' }}">
                    {{ $index + 1 }}
                </div>
                <div class="flex-1">
                    <p class="font-semibold capitalize">{{ $status }}</p>
                    @if($index == $currentStatusIndex)
                        <p class="text-sm text-blue-600">Current Status</p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Order Details -->
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h2 class="text-lg font-semibold mb-4">Order Details</h2>
    
    <div class="mb-4">
        <h3 class="font-semibold mb-2">Items</h3>
        @foreach($order->items as $item)
            <div class="flex justify-between border-b pb-2 mb-2">
                <div>
                    <p>{{ $item->product->name }}</p>
                    <p class="text-sm text-gray-500">{{ $item->quantity }} x Rp {{ number_format($item->unit_price, 0, ',', '.') }}</p>
                </div>
                <p class="font-semibold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</p>
            </div>
        @endforeach
    </div>

    <div class="border-t pt-4">
        <div class="flex justify-between mb-2">
            <span>Subtotal:</span>
            <span>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
        </div>
        <div class="flex justify-between mb-2">
            <span>Shipping:</span>
            <span>Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
        </div>
        <div class="flex justify-between font-bold text-lg">
            <span>Total:</span>
            <span>Rp {{ number_format($order->total, 0, ',', '.') }}</span>
        </div>
    </div>
</div>

<!-- Delivery Information -->
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h2 class="text-lg font-semibold mb-4">Delivery Information</h2>
    <p class="mb-2"><strong>Name:</strong> {{ $order->customer_name }}</p>
    <p class="mb-2"><strong>Phone:</strong> {{ $order->customer_phone }}</p>
    <p><strong>Address:</strong> {{ $order->customer_address }}</p>
</div>

<!-- Admin Notes -->
@if($order->admin_notes)
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
        <h2 class="text-lg font-semibold mb-2">Admin Notes</h2>
        <p>{{ $order->admin_notes }}</p>
    </div>
@endif

<!-- Chat Section -->
<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-lg font-semibold mb-4">Chat with Admin</h2>
    
    <div id="chatMessages" class="border rounded p-4 mb-4 h-64 overflow-y-auto bg-gray-50">
        <!-- Messages loaded by JavaScript -->
    </div>

    <form id="chatForm" class="flex gap-2">
        <input type="text" id="messageInput" placeholder="Type a message..."
               class="flex-1 border rounded px-3 py-2" required>
        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
            Send
        </button>
    </form>
</div>

<script>
const orderId = {{ $order->id }};

function loadMessages() {
    fetch(`/api/orders/${orderId}/messages`)
        .then(res => res.json())
        .then(data => {
            let html = '';
            data.data.forEach(msg => {
                const isOwn = msg.sender_id === {{ auth()->id() }};
                html += `
                    <div class="mb-3 ${isOwn ? 'text-right' : ''}">
                        <div class="${isOwn ? 'bg-blue-600 text-white' : 'bg-gray-200'} inline-block px-3 py-2 rounded">
                            <p class="text-xs font-semibold mb-1">${msg.sender_name}</p>
                            <p>${msg.content}</p>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">
                            ${new Date(msg.created_at).toLocaleTimeString()}
                        </p>
                    </div>
                `;
            });
            document.getElementById('chatMessages').innerHTML = html;
            document.getElementById('chatMessages').scrollTop = document.getElementById('chatMessages').scrollHeight;
        });
}

document.getElementById('chatForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const content = document.getElementById('messageInput').value;

    try {
        const response = await fetch(`/api/orders/${orderId}/messages`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('[name="_token"]').value,
            },
            body: JSON.stringify({ content })
        });

        if (response.ok) {
            document.getElementById('messageInput').value = '';
            loadMessages();
        }
    } catch (error) {
        alert('Error sending message');
    }
});

// Load messages on page load and poll every 5 seconds
loadMessages();
setInterval(loadMessages, 5000);
</script>
@endsection
