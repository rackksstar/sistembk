<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>

    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="bg-gray-100">

    <!-- Navbar -->
    <nav class="bg-white shadow px-6 py-4 flex justify-between items-center">

        <span class="font-bold text-blue-600 text-lg">
            BK System
        </span>

        @auth
        <div class="flex items-center gap-4">
            <span class="text-gray-600">
                {{ auth()->user()->name }}
            </span>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">
                    Logout
                </button>
            </form>
        </div>
        @endauth

    </nav>

    <!-- Content -->
    <div class="p-6">
        @yield('content')
    </div>

</body>
</html>
