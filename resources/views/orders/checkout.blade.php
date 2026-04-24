@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
<h1 class="text-2xl font-bold mb-6">Checkout</h1>

<form method="POST" action="/api/orders" id="checkoutForm" class="bg-white rounded-lg shadow p-6">
    @csrf

    <!-- Cart Items -->
    <div class="mb-6">
        <h2 class="text-lg font-semibold mb-4">Order Summary</h2>
        <div id="cartItems" class="space-y-3">
            <!-- Populated by JavaScript -->
        </div>
        <div class="border-t pt-3 mt-3">
            <div class="flex justify-between font-bold text-lg">
                <span>Total:</span>
                <span id="totalPrice">Rp 0</span>
            </div>
        </div>
    </div>

    <!-- Delivery Information -->
    <div class="mb-6">
        <h2 class="text-lg font-semibold mb-4">Delivery Information</h2>
        
        <div class="mb-4">
            <label class="block text-sm font-semibold mb-2">Full Name</label>
            <input type="text" name="customer_name" required
                   class="w-full border rounded px-3 py-2">
        </div>

        <div class="mb-4">
            <label class="block text-sm font-semibold mb-2">Phone Number</label>
            <input type="tel" name="customer_phone" required
                   placeholder="+62812345678"
                   class="w-full border rounded px-3 py-2">
        </div>

        <div class="mb-4">
            <label class="block text-sm font-semibold mb-2">Address</label>
            <textarea name="customer_address" required rows="4"
                      class="w-full border rounded px-3 py-2"></textarea>
        </div>
    </div>

    <!-- Hidden items -->
    <div id="cartItemsInput">
        <!-- Populated by JavaScript -->
    </div>

    <button type="submit" class="w-full bg-green-600 text-white py-3 rounded font-semibold hover:bg-green-700">
        Place Order (Cash on Delivery)
    </button>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
    
    if (cart.length === 0) {
        document.getElementById('cartItems').innerHTML = '<p class="text-gray-500">Your cart is empty.</p>';
        document.querySelector('button[type="submit"]').disabled = true;
        return;
    }

    let total = 0;
    let cartHTML = '';
    let itemsInput = '';

    // Fetch product details
    cart.forEach((item, index) => {
        fetch(`/api/products/${item.id}`)
            .then(res => res.json())
            .then(data => {
                const product = data.data;
                const subtotal = product.price * item.quantity;
                total += subtotal;

                cartHTML += `
                    <div class="flex justify-between border-b pb-2">
                        <div>
                            <p class="font-semibold">${product.name}</p>
                            <p class="text-sm text-gray-500">${item.quantity} x Rp ${number_format(product.price)}</p>
                        </div>
                        <p class="font-semibold">Rp ${number_format(subtotal)}</p>
                    </div>
                `;

                itemsInput += `<input type="hidden" name="items[${index}][product_id]" value="${item.id}">
                               <input type="hidden" name="items[${index}][quantity]" value="${item.quantity}">`;

                document.getElementById('cartItems').innerHTML = cartHTML;
                document.getElementById('cartItemsInput').innerHTML = itemsInput;
                document.getElementById('totalPrice').textContent = 'Rp ' + number_format(total);
            });
    });
});

function number_format(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

document.getElementById('checkoutForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const items = [];

    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
    cart.forEach((item, index) => {
        items.push({
            product_id: item.id,
            quantity: item.quantity
        });
    });

    try {
        const response = await fetch('/api/orders', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('[name="_token"]').value,
            },
            body: JSON.stringify({
                items: items,
                customer_name: formData.get('customer_name'),
                customer_phone: formData.get('customer_phone'),
                customer_address: formData.get('customer_address'),
            })
        });

        const result = await response.json();

        if (result.success) {
            localStorage.removeItem('cart');
            window.location.href = `/orders/${result.data.id}/confirmation`;
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Error placing order: ' + error.message);
    }
});
</script>
@endsection
