<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Informasi BK</title>

    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="bg-gray-100">

    <!-- Navbar -->
    <nav class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <h1 class="text-xl font-bold text-blue-600">
                BK System
            </h1>

            <div class="space-x-3">
                <a href="{{ route('login') }}"
                   class="px-4 py-2 text-sm text-blue-600 border border-blue-600 rounded hover:bg-blue-50">
                    Login
                </a>

                <a href="{{ route('register') }}"
                   class="px-4 py-2 text-sm bg-blue-600 text-white rounded hover:bg-blue-700">
                    Register
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="text-center py-16 bg-gradient-to-br from-blue-500 to-indigo-600 text-white">
        <h2 class="text-4xl font-bold mb-4">
            Sistem Informasi Bimbingan Konseling
        </h2>
        <p class="max-w-2xl mx-auto text-lg">
            Platform digital untuk membantu proses layanan konseling siswa secara mudah,
            cepat, dan efisien antara siswa dan guru BK.
        </p>
    </section>

    <!-- Role Section -->
    <section class="max-w-6xl mx-auto py-16 px-6">
        <h3 class="text-2xl font-semibold text-center mb-10 text-gray-800">
            Masuk Sebagai
        </h3>

        <div class="grid md:grid-cols-3 gap-6">

            <!-- Admin -->
            <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition text-center">
                <h4 class="text-xl font-semibold mb-2">👨‍💼 Admin</h4>
                <p class="text-gray-600 mb-4 text-sm">
                    Mengelola sistem, data pengguna, dan keseluruhan platform.
                </p>
                <a href="{{ route('login') }}"
                   class="inline-block px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                    Masuk
                </a>
            </div>

            <!-- Guru BK -->
            <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition text-center">
                <h4 class="text-xl font-semibold mb-2">👩‍🏫 Guru BK</h4>
                <p class="text-gray-600 mb-4 text-sm">
                    Mengelola jadwal konseling, data siswa, dan laporan layanan.
                </p>
                <a href="{{ route('login') }}"
                   class="inline-block px-4 py-2 bg-indigo-500 text-white rounded hover:bg-indigo-600">
                    Masuk
                </a>
            </div>

            <!-- Siswa -->
            <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition text-center">
                <h4 class="text-xl font-semibold mb-2">🎓 Siswa</h4>
                <p class="text-gray-600 mb-4 text-sm">
                    Mengajukan konseling, melihat jadwal, dan memberikan penilaian layanan.
                </p>
                <a href="{{ route('login') }}"
                   class="inline-block px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
                    Masuk
                </a>
            </div>

        </div>
    </section>

    <!-- Footer -->
    <footer class="text-center py-6 text-gray-500 text-sm">
        © {{ date('Y') }} Sistem Informasi BK. All rights reserved.
    </footer>

</body>
</html>
