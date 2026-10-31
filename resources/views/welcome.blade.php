<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Platform bimbingan konseling modern untuk siswa, guru BK, dan admin.">
    <title>BK System</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v=bk-system">
    <link rel="shortcut icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v=bk-system">
    @include('partials.theme-init')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100">
    @php
        $requiresLogoutConfirmation ??= false;
        $currentUser = auth()->user();
        $loginSiswa = route('login', ['role' => 'siswa']);
        $loginGuru = route('login', ['role' => 'guru']);
    @endphp

    <div class="relative min-h-screen overflow-hidden bg-[radial-gradient(ellipse_at_top_right,_#dbeafe_0%,_transparent_55%),linear-gradient(160deg,#f8fbff_0%,#eef4fb_48%,#e2ebf5_100%)] dark:bg-none dark:bg-slate-950">
        <div class="pointer-events-none absolute inset-0 hidden dark:block dark:bg-[radial-gradient(ellipse_at_top_right,_rgba(37,99,235,0.18)_0%,_transparent_50%),linear-gradient(160deg,#020617_0%,#0f172a_55%,#020617_100%)]" aria-hidden="true"></div>

        <header class="sticky top-4 z-50 px-4">
            <nav
                class="mx-auto flex max-w-7xl items-center justify-between rounded-full border border-white/80 bg-white/90 px-4 py-3 shadow-md shadow-blue-100/70 backdrop-blur dark:border-slate-700/80 dark:bg-slate-900/90 sm:px-6">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-3">
                    <span
                        class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-blue-600 text-sm font-bold text-white shadow-lg shadow-blue-500/25">BK</span>
                    <span class="text-base font-semibold tracking-wide text-slate-950 dark:text-white">BK System</span>
                </a>

                <div class="flex items-center gap-2 sm:gap-3">
                    <x-theme-toggle />
                    @if ($currentUser && ! $requiresLogoutConfirmation)
                        <a href="{{ route($currentUser->dashboardRoute()) }}"
                            class="inline-flex items-center justify-center rounded-full bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-500/25 transition hover:bg-blue-500 sm:px-5">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ $loginSiswa }}"
                            class="inline-flex items-center justify-center gap-2 rounded-full bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-500/25 transition hover:bg-blue-500 hover:shadow-blue-500/35 sm:px-5">
                            <svg class="h-4 w-4 opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6A2.25 2.25 0 0 0 5.25 5.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H3" />
                            </svg>
                            Login
                        </a>
                    @endif
                </div>
            </nav>
        </header>

        <main class="relative mx-auto max-w-7xl px-6 pb-16 pt-16 sm:px-8 sm:pt-20 lg:px-10">
            @if (session('status'))
                <div class="mb-8 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium leading-6 text-emerald-800 shadow-xs shadow-emerald-100 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-200">
                    {{ session('status') }}
                </div>
            @endif

            <section class="grid gap-10 lg:grid-cols-[1.08fr_0.92fr] lg:items-center">
                <div>
                    <p
                        class="inline-flex rounded-full border border-blue-100 bg-white/80 px-4 py-2 text-xs font-bold uppercase tracking-[0.24em] text-blue-600 shadow-xs dark:border-blue-900/50 dark:bg-slate-900/80">
                        SISTEM BK
                    </p>

                    <h1 class="mt-6 max-w-3xl text-4xl font-bold tracking-tight text-slate-950 dark:text-white sm:text-5xl lg:text-6xl">
                        Satu akses untuk Siswa dan Guru BK.
                    </h1>

                    <p class="mt-6 max-w-2xl text-base leading-8 text-slate-600 dark:text-slate-400 sm:text-lg">
                        Kelola pengajuan konseling, asesmen minat bakat, jadwal, laporan, data siswa, dan
                        informasi karier dalam satu platform.
                    </p>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center">
                        <a href="{{ $loginSiswa }}"
                            class="group inline-flex items-center justify-center gap-2 rounded-full bg-blue-600 px-7 py-3.5 text-sm font-semibold text-white shadow-lg shadow-blue-500/30 transition hover:-translate-y-0.5 hover:bg-blue-500 hover:shadow-xl hover:shadow-blue-500/35">
                            Login sekarang
                            <svg class="h-4 w-4 transition group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </a>
                        <a href="{{ route('guru.register') }}"
                            class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white px-6 py-3.5 text-sm font-semibold text-slate-700 shadow-md shadow-slate-200/70 transition hover:border-blue-200 hover:text-blue-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-blue-800 dark:hover:text-blue-300">
                            Daftar Guru BK
                        </a>
                    </div>

                    <div class="mt-10 grid gap-4 sm:grid-cols-2">
                        <a href="{{ $loginSiswa }}"
                            class="group relative overflow-hidden rounded-2xl border border-white bg-white/90 p-5 shadow-md shadow-blue-100/60 transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-lg dark:border-slate-700 dark:bg-slate-900/90 dark:hover:border-blue-800">
                            <div class="flex items-start justify-between gap-3">
                                <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-300">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.44 60.44 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 15.246c2.747 0 5.42.35 8.007.996a60.4 60.4 0 0 0-.491-6.347M12 15.246V21m0-5.754c2.747 0 5.42.35 8.007.996M12 15.246c-2.747 0-5.42.35-8.007.996M12 3v5.754m0 0A48.667 48.667 0 0 1 4.26 10.147M12 8.754A48.66 48.66 0 0 0 19.74 10.147" />
                                    </svg>
                                </span>
                                <span class="text-xs font-bold uppercase tracking-[0.16em] text-blue-600 opacity-0 transition group-hover:opacity-100 dark:text-blue-300">Masuk →</span>
                            </div>
                            <h2 class="mt-4 text-base font-bold text-slate-950 dark:text-white">Login Siswa</h2>
                            <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-400">Masuk ke dashboard siswa untuk konseling, asesmen, dan tryout.</p>
                        </a>
                        <a href="{{ $loginGuru }}"
                            class="group relative overflow-hidden rounded-2xl border border-white bg-white/90 p-5 shadow-md shadow-blue-100/60 transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-lg dark:border-slate-700 dark:bg-slate-900/90 dark:hover:border-blue-800">
                            <div class="flex items-start justify-between gap-3">
                                <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                    </svg>
                                </span>
                                <span class="text-xs font-bold uppercase tracking-[0.16em] text-blue-600 opacity-0 transition group-hover:opacity-100 dark:text-blue-300">Masuk →</span>
                            </div>
                            <h2 class="mt-4 text-base font-bold text-slate-950 dark:text-white">Login Guru BK</h2>
                            <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-400">Masuk ke dashboard Guru BK untuk layanan, laporan, dan instrumen.</p>
                        </a>
                    </div>
                </div>

                <div
                    class="rounded-2xl border border-white bg-white/80 p-5 shadow-lg shadow-blue-200/60 backdrop-blur dark:border-slate-700 dark:bg-slate-900/80 sm:p-6">
                    <div class="rounded-2xl bg-white p-6 shadow-md shadow-slate-200/70 dark:bg-slate-900">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.22em] text-blue-600">Keunggulan
                                    Website</p>
                                <h2 class="mt-2 text-2xl font-bold text-slate-950 dark:text-white">Lebih cepat, rapi, dan mudah dipantau
                                </h2>
                            </div>
                            <span
                                class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-blue-600 dark:bg-blue-950/40">BK</span>
                        </div>

                        <div class="mt-6 space-y-3">
                            <div class="rounded-2xl bg-blue-50 p-5 dark:bg-blue-950/40">
                                <p class="font-bold text-slate-950 dark:text-white">Pengajuan konseling terpusat</p>
                                <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-400">Siswa bisa mengirim keluhan, memilih
                                    Guru BK, dan memantau status tanpa proses manual.</p>
                            </div>
                            <div class="rounded-2xl bg-slate-100 p-5 dark:bg-slate-800">
                                <p class="font-bold text-slate-950 dark:text-white">Asesmen & lanjut kuliah</p>
                                <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-400">Minat bakat kerja, strategi belajar,
                                    kepribadian, serta rekomendasi prodi PCR dalam satu alur.</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 rounded-2xl bg-white p-6 shadow-md shadow-slate-200/70 dark:bg-slate-900">
                        <p class="text-xs font-bold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">Nilai Utama</p>
                        <div class="mt-5 grid gap-3 sm:grid-cols-2">
                            <div class="rounded-2xl bg-blue-50 p-4 dark:bg-blue-950/40">
                                <p class="text-2xl font-bold text-blue-600">01</p>
                                <p class="mt-2 text-sm font-semibold text-slate-950 dark:text-white">Data tersimpan aman</p>
                                <p class="mt-1 text-sm leading-6 text-slate-600 dark:text-slate-400">Semua data masuk database sekolah.</p>
                            </div>
                            <div class="rounded-2xl bg-slate-100 p-4 dark:bg-slate-800">
                                <p class="text-2xl font-bold text-blue-600">02</p>
                                <p class="mt-2 text-sm font-semibold text-slate-950 dark:text-white">Laporan siap cetak</p>
                                <p class="mt-1 text-sm leading-6 text-slate-600 dark:text-slate-400">Hasil konseling dan evaluasi lebih
                                    rapi.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <section id="fitur" class="bg-white px-6 py-16 dark:bg-slate-900 sm:px-8 lg:px-10">
        <div
            class="mx-auto max-w-7xl rounded-2xl border border-blue-100 bg-slate-50 p-6 shadow-md shadow-blue-100/60 dark:border-blue-900/50 dark:bg-slate-800/60 sm:p-8">
            <div class="grid gap-6 lg:grid-cols-[0.9fr_1.1fr_1.1fr] lg:items-stretch">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-blue-600">Fitur unggulan</p>
                    <h2 class="mt-3 text-2xl font-bold text-slate-950 dark:text-white">Semua alat BK dalam satu tempat.</h2>
                </div>

                <div class="rounded-xl border border-white bg-white p-6 shadow-md shadow-slate-200/70 dark:border-slate-700 dark:bg-slate-900">
                    <h3 class="text-base font-bold text-slate-950 dark:text-white">Manajemen Jadwal</h3>
                    <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-400">Atur dan lihat jadwal konseling dengan tampilan
                        yang rapi, jelas, dan mudah dipantau.</p>
                </div>

                <div class="rounded-xl border border-white bg-white p-6 shadow-md shadow-slate-200/70 dark:border-slate-700 dark:bg-slate-900">
                    <h3 class="text-base font-bold text-slate-950 dark:text-white">Data Siswa & Laporan</h3>
                    <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-400">Kelola data siswa, riwayat sesi, hasil konseling,
                        evaluasi, dan laporan dalam satu panel.</p>
                </div>
            </div>
        </div>
    </section>

    <footer class="border-t border-blue-100 bg-white py-8 text-slate-500 dark:border-blue-900/50 dark:bg-slate-900 dark:text-slate-400">
        <div
            class="mx-auto flex max-w-7xl flex-col gap-4 px-6 text-sm sm:flex-row sm:items-center sm:justify-between sm:px-8 lg:px-10">
            <p>&copy; 2026 BK System</p>
            <div class="flex flex-wrap items-center gap-5">
                <a href="#" class="transition hover:text-blue-600">Tentang</a>
                <a href="#fitur" class="transition hover:text-blue-600">Fitur</a>
                <a href="#" class="transition hover:text-blue-600">Kontak</a>
            </div>
        </div>
    </footer>

    @if ($requiresLogoutConfirmation && $currentUser)
        <div class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/60 px-4 backdrop-blur-xs"
            role="dialog" aria-modal="true" aria-labelledby="logout-confirmation-title">
            <div
                class="w-full max-w-md rounded-2xl border border-white bg-white p-6 text-center shadow-2xl shadow-slate-950/20 dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs font-bold uppercase tracking-[0.24em] text-blue-600">Konfirmasi Logout</p>
                <h2 id="logout-confirmation-title" class="mt-3 text-2xl font-bold text-slate-950 dark:text-white">Yakin ingin keluar?
                </h2>
                <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-400">
                    Anda masih login sebagai {{ $currentUser->name }}. Jika lanjut ke landing page, sesi semua role akan
                    diakhiri dan Anda harus login ulang untuk masuk dashboard.
                </p>

                <div class="mt-6 flex flex-col items-center justify-center gap-3 sm:flex-row">
                    <a href="{{ route($currentUser->dashboardRoute()) }}"
                        class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-blue-200 hover:text-blue-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-blue-800 dark:hover:text-blue-300">
                        Batal
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="inline-flex justify-center">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center justify-center rounded-full bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-500">
                            Ya, logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif
</body>

</html>
