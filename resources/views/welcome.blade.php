<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Platform bimbingan konseling modern untuk siswa, guru BK, dan admin.">
    <title>Sistem Informasi BK</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-50 text-slate-900">
    @php
        $requiresLogoutConfirmation ??= false;
        $currentUser = auth()->user();
    @endphp

    <div class="min-h-screen bg-[linear-gradient(135deg,#f8fbff_0%,#edf5ff_44%,#dbe7f5_100%)]">
        <header class="sticky top-4 z-50 px-4">
            <nav
                class="mx-auto flex max-w-7xl items-center justify-between rounded-full border border-white/80 bg-white/85 px-4 py-3 shadow-md shadow-blue-100/70 backdrop-blur sm:px-6">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-3">
                    <span
                        class="inline-flex h-11 w-11 items-center justify-center rounded-full bg-blue-600 text-sm font-bold text-white shadow-lg shadow-blue-500/25">BK</span>
                    <span class="text-base font-semibold tracking-wide text-slate-950">BK System</span>
                </a>

                <div class="flex items-center gap-2 sm:gap-3">
                    <a href="{{ route('login', ['role' => 'admin']) }}"
                        class="inline-flex items-center justify-center rounded-full bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-200 hover:text-slate-950">
                        Login Admin
                    </a>
                </div>
            </nav>
        </header>

        <main class="mx-auto max-w-7xl px-6 pb-16 pt-20 sm:px-8 lg:px-10">
            <section class="grid gap-10 lg:grid-cols-[1.08fr_0.92fr] lg:items-center">
                <div>
                    <p
                        class="inline-flex rounded-full border border-blue-100 bg-white/80 px-4 py-2 text-xs font-bold uppercase tracking-[0.24em] text-blue-600 shadow-sm">
                        SISTEM BK MODERN
                    </p>

                    <h1 class="mt-6 max-w-3xl text-4xl font-bold tracking-tight text-slate-950 sm:text-5xl lg:text-6xl">
                        Satu akses untuk Siswa dan Guru BK.
                    </h1>

                    <p class="mt-6 max-w-2xl text-base leading-8 text-slate-600 sm:text-lg">
                        Kelola pengajuan konseling, approval Guru BK, jadwal, laporan, data siswa, kelas bimbingan, dan
                        informasi karier dalam panel yang rapi.
                    </p>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center">
                        <a href="{{ route('guru.register') }}"
                            class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white px-6 py-3 text-sm font-semibold text-slate-700 shadow-md shadow-slate-200/70 transition hover:border-blue-200 hover:text-blue-700">
                            Daftar Guru BK
                        </a>
                    </div>

                    <div class="mt-8 grid gap-4 sm:grid-cols-2">
                        <a href="{{ route('login', ['role' => 'siswa']) }}"
                            class="rounded-2xl border border-white bg-white/85 p-5 shadow-md shadow-blue-100/60 transition hover:-translate-y-0.5 hover:shadow-lg">
                            <h2 class="text-base font-bold text-slate-950">Siswa</h2>
                            <p class="mt-2 text-sm leading-6 text-slate-600">Login untuk masuk ke dashboard siswa.</p>
                        </a>
                        <a href="{{ route('login', ['role' => 'guru']) }}"
                            class="rounded-2xl border border-white bg-white/85 p-5 shadow-md shadow-blue-100/60 transition hover:-translate-y-0.5 hover:shadow-lg">
                            <h2 class="text-base font-bold text-slate-950">Guru BK</h2>
                            <p class="mt-2 text-sm leading-6 text-slate-600">Login untuk masuk ke dashboard Guru BK.</p>
                        </a>
                    </div>
                </div>

                <div
                    class="rounded-2xl border border-white bg-white/80 p-5 shadow-lg shadow-blue-200/60 backdrop-blur sm:p-6">
                    <div class="rounded-2xl bg-white p-6 shadow-md shadow-slate-200/70">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.22em] text-blue-600">Keunggulan
                                    Website</p>
                                <h2 class="mt-2 text-2xl font-bold text-slate-950">Lebih cepat, rapi, dan mudah dipantau
                                </h2>
                            </div>
                            <span
                                class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-blue-600">BK</span>
                        </div>

                        <div class="mt-6 space-y-3">
                            <div class="rounded-2xl bg-blue-50 p-5">
                                <p class="font-bold text-slate-950">Pengajuan konseling terpusat</p>
                                <p class="mt-2 text-sm leading-6 text-slate-600">Siswa bisa mengirim keluhan, memilih
                                    Guru BK, dan memantau status tanpa proses manual.</p>
                            </div>
                            <div class="rounded-2xl bg-slate-100 p-5">
                                <p class="font-bold text-slate-950">Informasi bimbingan konseling</p>
                                <p class="mt-2 text-sm leading-6 text-slate-600">Siswa dapat melihat layanan BK,
                                    informasi karier, dan arahan konseling yang membantu pengembangan diri.</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 rounded-2xl bg-white p-6 shadow-md shadow-slate-200/70">
                        <p class="text-xs font-bold uppercase tracking-[0.22em] text-slate-500">Nilai Utama</p>
                        <div class="mt-5 grid gap-3 sm:grid-cols-2">
                            <div class="rounded-2xl bg-blue-50 p-4">
                                <p class="text-2xl font-bold text-blue-600">01</p>
                                <p class="mt-2 text-sm font-semibold text-slate-950">Data tersimpan aman</p>
                                <p class="mt-1 text-sm leading-6 text-slate-600">Semua data masuk database sekolah.</p>
                            </div>
                            <div class="rounded-2xl bg-slate-100 p-4">
                                <p class="text-2xl font-bold text-blue-600">02</p>
                                <p class="mt-2 text-sm font-semibold text-slate-950">Laporan siap cetak</p>
                                <p class="mt-1 text-sm leading-6 text-slate-600">Hasil konseling dan evaluasi lebih
                                    rapi.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <section id="fitur" class="bg-white px-6 py-16 sm:px-8 lg:px-10">
        <div
            class="mx-auto max-w-7xl rounded-2xl border border-blue-100 bg-slate-50 p-6 shadow-md shadow-blue-100/60 sm:p-8">
            <div class="grid gap-6 lg:grid-cols-[0.9fr_1.1fr_1.1fr] lg:items-stretch">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-blue-600">Fitur unggulan</p>
                    <h2 class="mt-3 text-2xl font-bold text-slate-950">Semua alat BK dalam satu tempat.</h2>
                </div>

                <div class="rounded-xl border border-white bg-white p-6 shadow-md shadow-slate-200/70">
                    <h3 class="text-base font-bold text-slate-950">Manajemen Jadwal</h3>
                    <p class="mt-3 text-sm leading-6 text-slate-600">Atur dan lihat jadwal konseling dengan tampilan
                        yang rapi, jelas, dan mudah dipantau.</p>
                </div>

                <div class="rounded-xl border border-white bg-white p-6 shadow-md shadow-slate-200/70">
                    <h3 class="text-base font-bold text-slate-950">Data Siswa & Laporan</h3>
                    <p class="mt-3 text-sm leading-6 text-slate-600">Kelola data siswa, riwayat sesi, hasil konseling,
                        evaluasi, dan laporan dalam satu panel.</p>
                </div>
            </div>
        </div>
    </section>

    <footer class="border-t border-blue-100 bg-white py-8 text-slate-500">
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
        <div class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/60 px-4 backdrop-blur-sm"
            role="dialog" aria-modal="true" aria-labelledby="logout-confirmation-title">
            <div
                class="w-full max-w-md rounded-2xl border border-white bg-white p-6 text-center shadow-2xl shadow-slate-950/20">
                <p class="text-xs font-bold uppercase tracking-[0.24em] text-blue-600">Konfirmasi Logout</p>
                <h2 id="logout-confirmation-title" class="mt-3 text-2xl font-bold text-slate-950">Yakin ingin keluar?
                </h2>
                <p class="mt-3 text-sm leading-6 text-slate-600">
                    Anda masih login sebagai {{ $currentUser->name }}. Jika lanjut ke landing page, sesi semua role akan
                    diakhiri dan Anda harus login ulang untuk masuk dashboard.
                </p>

                <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-center">
                    <a href="{{ route($currentUser->dashboardRoute()) }}"
                        class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-blue-200 hover:text-blue-700">
                        Batal
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="inline-flex w-full items-center justify-center rounded-full bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-500">
                            Ya, logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif
</body>

</html>
