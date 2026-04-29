@props(['role' => 'login'])

@php
    $roleConfig = [
        'login' => [
            'badge' => 'PORTAL AKSES',
            'title' => 'Masuk sesuai peran, kelola layanan BK dengan tenang.',
            'description' => 'Satu pintu login untuk Siswa dan Guru BK dengan dashboard yang otomatis menyesuaikan role.',
            'accent' => 'from-blue-600 to-sky-400',
            'cards' => [
                ['title' => 'Guru BK', 'text' => 'Approve konseling, susun jadwal, dan isi laporan.'],
                ['title' => 'Siswa', 'text' => 'Masuk kelas, ajukan konseling, dan baca info karier.'],
                ['title' => 'Aman', 'text' => 'Akses panel mengikuti role akun yang login.'],
            ],
        ],
        'siswa' => [
            'badge' => 'AKUN SISWA',
            'title' => 'Mulai akses layanan konseling dari dashboard siswa.',
            'description' => 'Daftar sebagai siswa untuk masuk kelas bimbingan, mengajukan keluhan, memilih Guru BK, dan memantau status konseling.',
            'accent' => 'from-blue-600 to-indigo-400',
            'cards' => [
                ['title' => 'Ajukan Konseling', 'text' => 'Isi keluhan dan pilih Guru BK yang dituju.'],
                ['title' => 'Masuk Kelas', 'text' => 'Gunakan kode kelas dari sekolah.'],
                ['title' => 'Karier', 'text' => 'Baca informasi karier dalam card modern.'],
            ],
        ],
        'guru' => [
            'badge' => 'AKUN GURU BK',
            'title' => 'Ajukan akun Guru BK untuk mengelola layanan konseling.',
            'description' => 'Pendaftaran Guru BK masuk status pending. Setelah admin menyetujui, Guru BK bisa approve sesi, membuat jadwal, dan menulis laporan.',
            'accent' => 'from-sky-500 to-blue-600',
            'cards' => [
                ['title' => 'Status Pending', 'text' => 'Akun diverifikasi admin sebelum aktif.'],
                ['title' => 'Approval Sesi', 'text' => 'Tinjau pengajuan konseling siswa.'],
                ['title' => 'Laporan', 'text' => 'Isi hasil konseling dan evaluasi.'],
            ],
        ],
    ][$role] ?? null;

    $roleConfig ??= [
        'badge' => 'BK SYSTEM',
        'title' => 'Akses layanan BK sekolah.',
        'description' => 'Gunakan akun Anda untuk masuk ke dashboard sesuai role.',
        'accent' => 'from-blue-600 to-sky-400',
        'cards' => [
            ['title' => 'Aman', 'text' => 'Data dikelola sesuai role pengguna.'],
            ['title' => 'Rapi', 'text' => 'Panel modern dan mudah dipindai.'],
            ['title' => 'Cepat', 'text' => 'Akses fitur utama dalam beberapa klik.'],
        ],
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Sistem Informasi BK') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
    <div class="relative min-h-screen overflow-hidden bg-[linear-gradient(135deg,#f8fbff_0%,#edf5ff_44%,#dbe7f5_100%)]">
        <div class="absolute -left-28 -top-20 h-96 w-96 rotate-[-28deg] rounded-[42%] bg-gradient-to-br {{ $roleConfig['accent'] }} opacity-20"></div>
        <div class="absolute -right-32 bottom-20 h-96 w-96 rotate-[30deg] rounded-[44%] bg-gradient-to-br {{ $roleConfig['accent'] }} opacity-20"></div>
        <div class="absolute inset-x-0 bottom-0 h-40 bg-gradient-to-t from-white/90 to-transparent"></div>

        <div class="relative mx-auto flex min-h-screen max-w-7xl flex-col px-4 py-4 sm:px-6 lg:px-8">
            <header class="sticky top-4 z-30 flex items-center justify-between gap-4 rounded-full border border-white/80 bg-white/85 px-4 py-3 shadow-md shadow-blue-100/70 backdrop-blur sm:px-6">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-3 text-slate-950 transition hover:opacity-90">
                    <span class="inline-flex h-11 w-11 items-center justify-center rounded-full bg-blue-600 text-sm font-bold text-white shadow-lg shadow-blue-500/25">BK</span>
                    <span class="text-lg font-semibold tracking-wide">BK System</span>
                </a>
                <div class="hidden items-center gap-3 text-sm text-slate-600 sm:flex">
                    @if($role === 'login')
                        <span class="rounded-full bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-500">Admin</span>
                    @endif
                    <a href="{{ route('login') }}" class="rounded-full bg-slate-100 px-4 py-2 font-semibold shadow-sm transition hover:bg-slate-200 hover:text-slate-950">Masuk</a>
                    <a href="{{ route('register') }}" class="rounded-full bg-blue-600 px-4 py-2 font-semibold text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-500">Daftar Siswa</a>
                </div>
            </header>

            <div class="grid flex-1 gap-8 py-12 lg:grid-cols-[1fr_480px] lg:items-center">
                <section class="hidden lg:block">
                    <div class="max-w-xl">
                        <p class="inline-flex rounded-full border border-white/80 bg-white/70 px-4 py-2 text-sm font-bold uppercase tracking-[0.24em] text-blue-600 shadow-sm backdrop-blur">{{ $roleConfig['badge'] }}</p>
                        <h1 class="mt-6 text-5xl font-bold tracking-tight text-slate-950">{{ $roleConfig['title'] }}</h1>
                        <p class="mt-5 text-base leading-8 text-slate-600">{{ $roleConfig['description'] }}</p>
                    </div>

                    <div class="mt-8 grid max-w-2xl gap-4 sm:grid-cols-3">
                        @foreach($roleConfig['cards'] as $card)
                            <div class="rounded-2xl border border-white bg-white/80 p-5 shadow-md shadow-blue-100/60 backdrop-blur">
                                <p class="font-bold text-slate-950">{{ $card['title'] }}</p>
                                <p class="mt-2 text-sm leading-6 text-slate-600">{{ $card['text'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>

                <main class="mx-auto w-full max-w-md">
                    <div class="overflow-hidden rounded-2xl border border-white bg-white shadow-lg shadow-blue-200/60">
                        <div class="h-2 bg-gradient-to-r {{ $roleConfig['accent'] }}"></div>
                        <div class="p-8">
                        {{ $slot }}
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>
</body>
</html>
