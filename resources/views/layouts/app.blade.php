<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - E-Commerce</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .mobile-first { max-width: 640px; margin: 0 auto; }
    </style>
</head>
<body class="bg-gray-50">
    <nav class="bg-white shadow">
        <div class="mobile-first px-4 py-4 flex justify-between items-center">
            <a href="/" class="text-xl font-bold text-blue-600">MyStore</a>
            <div class="flex gap-4">
                @auth
                    <span class="text-sm text-gray-600">{{ auth()->user()->name }}</span>
                    <a href="/orders" class="text-blue-600 text-sm">Orders</a>
                    <form method="POST" action="/logout" class="inline">
                        @csrf
                        <button type="submit" class="text-red-600 text-sm">Logout</button>
                    </form>
                @else
                    <a href="/login" class="text-blue-600 text-sm">Login</a>
                    <a href="/register" class="text-blue-600 text-sm">Register</a>
                @endauth
            </div>
        </div>
    </nav>

    <main class="mobile-first px-4 py-8">
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
