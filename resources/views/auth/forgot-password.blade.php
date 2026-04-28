<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lupa Password - Sistem Informasi BK</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-100">

<div class="min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-md bg-white/80 backdrop-blur-lg rounded-2xl shadow-xl p-8">

        <!-- Title -->
        <h2 class="text-2xl font-bold text-gray-800 mb-2 text-center">
            Lupa Password 🔐
        </h2>
        <p class="text-center text-gray-500 mb-6 text-sm">
            Masukkan email kamu untuk mendapatkan link reset password
        </p>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4 text-green-600 text-sm text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf

            <!-- Email -->
            <div>
                <label class="text-sm text-gray-600">Email</label>
                <input type="email" name="email"
                    value="{{ old('email') }}"
                    required autofocus
                    class="w-full mt-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">

                <x-input-error :messages="$errors->get('email')" class="mt-1 text-sm text-red-500" />
            </div>

            <!-- Button -->
            <button type="submit"
                class="w-full bg-gradient-to-r from-blue-500 to-indigo-600 text-white py-2 rounded-lg hover:opacity-90 transition">
                Kirim Link Reset
            </button>

            <!-- Back to login -->
            <p class="text-center text-sm text-gray-600 mt-4">
                Sudah ingat password?
                <a href="{{ route('login') }}" class="text-blue-500 hover:underline font-medium">
                    Login
                </a>
            </p>
        </form>
    </div>
</div>

</body>
</html>
