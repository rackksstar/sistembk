<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Sistem Informasi BK</title>
    @vite('resources/css/app.css')
</head>
<body>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-500 to-blue-600">

        <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-8">

            <!-- Title -->
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-2">
                Daftar Akun
            </h2>
            <p class="text-center text-gray-500 mb-6">
                Buat akun untuk menggunakan sistem BK
            </p>

            <form method="POST" action="{{ route('register') }}" class="space-y-4">
                @csrf

                <!-- Name -->
                <div>
                    <label class="text-sm text-gray-600">Nama</label>
                    <input type="text" name="name"
                        value="{{ old('name') }}"
                        required autofocus
                        class="w-full mt-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none">

                    <x-input-error :messages="$errors->get('name')" class="mt-1 text-sm text-red-500" />
                </div>

                <!-- Email -->
                <div>
                    <label class="text-sm text-gray-600">Email</label>
                    <input type="email" name="email"
                        value="{{ old('email') }}"
                        required
                        class="w-full mt-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none">

                    <x-input-error :messages="$errors->get('email')" class="mt-1 text-sm text-red-500" />
                </div>

                <!-- Password -->
                <div>
                    <label class="text-sm text-gray-600">Password</label>
                    <input type="password" name="password"
                        required
                        class="w-full mt-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none">

                    <x-input-error :messages="$errors->get('password')" class="mt-1 text-sm text-red-500" />
                </div>

                <!-- Confirm Password -->
                <div>
                    <label class="text-sm text-gray-600">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation"
                        required
                        class="w-full mt-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none">

                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1 text-sm text-red-500" />
                </div>

                <!-- Button -->
                <button type="submit"
                    class="w-full bg-indigo-500 text-white py-2 rounded-lg hover:bg-indigo-600 transition">
                    Register
                </button>

                <!-- Login -->
                <p class="text-center text-sm text-gray-600 mt-4">
                    Sudah punya akun?
                    <a href="{{ route('login') }}" class="text-indigo-500 hover:underline">
                        Login
                    </a>
                </p>

            </form>

        </div>
    </div>
</body>
</html>
