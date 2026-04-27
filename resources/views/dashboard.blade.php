@extends('layouts.app')

@section('content')

<div class="max-w-5xl mx-auto">

    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">
            Dashboard
        </h1>
        <p class="text-gray-500">
            Selamat datang, {{ auth()->user()->name }}
        </p>
    </div>

    <!-- Card Menu -->
    <div class="grid md:grid-cols-3 gap-6">

        <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition">
            <h3 class="font-semibold text-lg mb-2">📅 Booking</h3>
            <p class="text-gray-500 text-sm">
                Ajukan jadwal konseling dengan guru BK
            </p>
        </div>

        <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition">
            <h3 class="font-semibold text-lg mb-2">📄 Riwayat</h3>
            <p class="text-gray-500 text-sm">
                Lihat riwayat konseling yang pernah dilakukan
            </p>
        </div>

        <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition">
            <h3 class="font-semibold text-lg mb-2">⭐ Penilaian</h3>
            <p class="text-gray-500 text-sm">
                Berikan penilaian terhadap layanan BK
            </p>
        </div>

    </div>

</div>

@endsection
