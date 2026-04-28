<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Sistem Informasi BK</title>
    @vite('resources/css/app.css')
</head>
<body>
   <div class="min-h-screen grid grid-cols-1 md:grid-cols-2">

        <!-- LEFT SIDE (Branding) -->
        <div class="hidden md:flex flex-col justify-center items-center bg-gradient-to-br from-blue-600 to-indigo-700 text-white p-10">
            <h1 class="text-4xl font-bold mb-4 text-center">
                Sistem Informasi BK
            </h1>
            <p class="text-center text-lg opacity-90 max-w-md">
                Platform digital untuk membantu layanan bimbingan konseling
                antara siswa dan guru secara efektif dan efisien.
            </p>
        </div>

        <!-- RIGHT SIDE (Form) -->
        <div class="flex items-center justify-center bg-gray-100 p-6">
            <div class="w-full max-w-md bg-white/80 backdrop-blur-lg rounded-2xl shadow-xl p-8">

                <!-- Title -->
                <h2 class="text-2xl font-bold text-gray-800 mb-2 text-center">
                    Welcome Back 👋
                </h2>
                <p class="text-center text-gray-500 mb-6">
                    Silakan login ke akun kamu
                </p>

                <!-- Session Status -->
                <x-auth-session-status class="mb-4 text-green-600 text-sm text-center" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="space-y-4">
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

                    <!-- Password -->
                    <div>
                        <label class="text-sm text-gray-600">Password</label>
                        <input type="password" name="password"
                            required
                            class="w-full mt-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">

                        <x-input-error :messages="$errors->get('password')" class="mt-1 text-sm text-red-500" />
                    </div>

                    <!-- Remember -->
                    <div class="flex items-center justify-between text-sm">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="remember" class="rounded">
                            <span class="text-gray-600">Remember me</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-blue-500 hover:underline">
                                Lupa password?
                            </a>
                        @endif
                    </div>

                    <!-- Button -->
                    <button type="submit"
                        class="w-full bg-gradient-to-r from-blue-500 to-indigo-600 text-white py-2 rounded-lg hover:opacity-90 transition">
                        Login
                    </button>

                    <!-- Register -->
                    <p class="text-center text-sm text-gray-600 mt-4">
                        Belum punya akun?
                        <a href="{{ route('register') }}" class="text-blue-500 hover:underline font-medium">
                            Daftar
                        </a>
                    </p>

                </form>
            </div>
        </div>

    </div>
</body>
</html>
