@extends('layouts.app')

@section('title', 'Products')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold mb-4">Our Products</h1>
    
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        @forelse($products as $product)
            <div class="bg-white rounded-lg shadow p-4">
                @if($product->image_path)
                    <img src="{{ Storage::url($product->image_path) }}" 
                         alt="{{ $product->name }}"
                         class="w-full h-48 object-cover rounded mb-3">
                @endif
                
                <h2 class="text-lg font-semibold mb-2">{{ $product->name }}</h2>
                
                <p class="text-gray-600 text-sm mb-3">
                    {{ Str::limit($product->description, 100) }}
                </p>
                
                <div class="flex justify-between items-center mb-3">
                    <span class="text-2xl font-bold text-blue-600">
                        Rp {{ number_format($product->price, 0, ',', '.') }}
                    </span>
                    <span class="text-xs bg-gray-200 px-2 py-1 rounded">
                        {{ $product->stock }} in stock
                    </span>
                </div>
                
                <div class="flex gap-2">
                    <a href="/products/{{ $product->id }}" 
                       class="flex-1 bg-blue-600 text-white py-2 rounded text-center text-sm font-semibold hover:bg-blue-700">
                        View Details
                    </a>
                    @auth
                        <button onclick="addToCart({{ $product->id }})" 
                                class="flex-1 bg-green-600 text-white py-2 rounded text-sm font-semibold hover:bg-green-700">
                            Add to Cart
                        </button>
                    @else
                        <a href="/login" class="flex-1 bg-green-600 text-white py-2 rounded text-center text-sm font-semibold hover:bg-green-700">
                            Add to Cart
                        </a>
                    @endauth
                </div>
            </div>
        @empty
            <p class="text-gray-500 col-span-2">No products available yet.</p>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $products->links() }}
    </div>
</div>

<script>
function addToCart(productId) {
    let cart = JSON.parse(localStorage.getItem('cart') || '[]');
    let item = cart.find(i => i.id === productId);
    
    if (item) {
        item.quantity += 1;
    } else {
        cart.push({ id: productId, quantity: 1 });
    }
    
    localStorage.setItem('cart', JSON.stringify(cart));
    alert('Added to cart!');
}
</script>
@endsection
