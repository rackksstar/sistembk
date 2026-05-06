<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Dashboard sistem bimbingan konseling untuk admin, guru BK, dan siswa.">
    <title>{{ config('app.name', 'BK System') }} | Dashboard</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="bg-[#f5f8ff] text-slate-900" x-data="{ logoutOpen: false }">
    @php
        $user = auth()->user();
        $roleLabel = ['admin' => 'Admin', 'guru' => 'Guru BK', 'siswa' => 'Siswa'][$user?->role] ?? 'Pengguna';
        $dashboardRoute = $user ? route($user->dashboardRoute()) : route('login');
        $menu = [
            ['label' => 'Dashboard', 'href' => $dashboardRoute, 'active' => request()->routeIs('*.dashboard') || request()->routeIs('dashboard')],
        ];

        if ($user?->role === 'admin') {
            $menu[] = ['label' => 'Approval Guru BK', 'href' => route('admin.approvals.index'), 'active' => request()->routeIs('admin.approvals.*')];
            $menu[] = ['label' => 'Manajemen Pengguna', 'href' => route('admin.users.index'), 'active' => request()->routeIs('admin.users.*')];
            $menu[] = ['label' => 'Data Siswa', 'href' => route('admin.students.index'), 'active' => request()->routeIs('admin.students.*')];
            $menu[] = ['label' => 'Kelas Bimbingan', 'href' => route('admin.guidance-classes.index'), 'active' => request()->routeIs('admin.guidance-classes.*')];
            $menu[] = ['label' => 'Konseling & Laporan', 'href' => route('admin.consultations.index'), 'active' => request()->routeIs('admin.consultations.*')];
            $menu[] = ['label' => 'Informasi Karier', 'href' => route('admin.careers.index'), 'active' => request()->routeIs('admin.careers.*')];
            $menu[] = ['label' => 'Sekolah', 'href' => route('admin.sekolah.index'), 'active' => request()->routeIs('admin.sekolah.*')];
            $menu[] = ['label' => 'Kelas', 'href' => route('admin.kelas.index'), 'active' => request()->routeIs('admin.kelas.*')];
            $menu[] = ['label' => 'Guru BK', 'href' => route('admin.guru-bk.index'), 'active' => request()->routeIs('admin.guru-bk.*')];
            $menu[] = ['label' => 'Master Pertanyaan', 'href' => route('admin.master-pertanyaan.index'), 'active' => request()->routeIs('admin.master-pertanyaan.*')];
            $menu[] = ['label' => 'Kategori Postingan', 'href' => route('admin.kategori-postingan.index'), 'active' => request()->routeIs('admin.kategori-postingan.*')];
        }

        if ($user?->role === 'guru') {
            $menu[] = ['label' => 'Data Siswa', 'href' => route('guru.students.index'), 'active' => request()->routeIs('guru.students.*')];
            $menu[] = ['label' => 'Konseling', 'href' => route('guru.consultations.index'), 'active' => request()->routeIs('guru.consultations.*')];
        }

        if ($user?->role === 'siswa') {
            $menu[] = ['label' => 'Asesmen Mandiri', 'href' => route('siswa.assessments.index'), 'active' => request()->routeIs('siswa.assessments.*')];
            $menu[] = ['label' => 'Informasi Karier', 'href' => route('siswa.careers.index'), 'active' => request()->routeIs('siswa.careers.*')];
        }

        if ($user?->role !== 'siswa') {
            $menu[] = ['label' => 'Profil', 'href' => route('profile.edit'), 'active' => request()->routeIs('profile.*')];
        }
    @endphp

    <div class="min-h-screen">
        <header class="sticky top-0 z-30 border-b border-blue-100 bg-white/90 shadow-sm backdrop-blur">
            <div class="mx-auto flex max-w-[1480px] items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                <a href="{{ $dashboardRoute }}" class="inline-flex items-center gap-3 text-slate-900">
                    <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-600 text-sm font-bold text-white shadow-lg shadow-blue-500/20">BK</span>
                    <div>
                        <p class="font-semibold leading-5">BK System</p>
                        <p class="text-xs text-slate-500">{{ $roleLabel }} panel</p>
                    </div>
                </a>

                <div class="flex items-center gap-3">
                    <div class="hidden text-right sm:block">
                        <p class="text-sm font-medium text-slate-900">{{ $user?->name }}</p>
                        <p class="text-xs text-slate-500">{{ $user?->email }}</p>
                    </div>
                    <button type="button" x-on:click="logoutOpen = true" class="rounded-2xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500">Keluar</button>
                </div>
            </div>
        </header>

        <div class="mx-auto grid max-w-[1480px] grid-cols-1 gap-6 px-4 py-6 sm:px-6 lg:grid-cols-[280px_minmax(0,1fr)] lg:px-8">
            <aside class="overflow-hidden rounded-3xl border border-blue-100 bg-white shadow-sm lg:sticky lg:top-24 lg:h-[calc(100vh-120px)]">
                <div class="bg-[linear-gradient(135deg,#2563eb_0%,#60a5fa_100%)] p-5 text-white">
                    <p class="text-sm font-semibold">{{ $roleLabel }} Workspace</p>
                    <p class="mt-2 text-xs leading-5 text-blue-50">Navigasi cepat untuk pekerjaan harian BK.</p>
                </div>
                <div class="p-4">
                <nav class="space-y-2" aria-label="Menu utama">
                    @foreach($menu as $item)
                        <a href="{{ $item['href'] }}" class="flex items-center justify-between rounded-2xl px-4 py-3 text-sm font-semibold transition {{ $item['active'] ? 'bg-blue-50 text-blue-700 ring-1 ring-blue-100' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                            <span>{{ $item['label'] }}</span>
                            @if($item['active'])
                                <span class="h-2 w-2 rounded-full bg-blue-600"></span>
                            @endif
                        </a>
                    @endforeach
                </nav>

                <div class="mt-6 rounded-3xl border border-blue-100 bg-blue-50 p-5">
                    <p class="text-sm font-semibold text-slate-900">Status akses</p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Anda masuk sebagai {{ $roleLabel }}. Menu dan data disesuaikan otomatis berdasarkan role.</p>
                </div>
                </div>
            </aside>

            <main class="min-w-0">
                @isset($header)
                    <div class="mb-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        {{ $header }}
                    </div>
                @endisset

                @isset($slot)
                    {{ $slot }}
                @else
                    @yield('content')
                @endisset
            </main>
        </div>
    </div>

    <div
        x-cloak
        x-show="logoutOpen"
        x-transition.opacity
        class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/60 px-4 backdrop-blur-sm"
        role="dialog"
        aria-modal="true"
        aria-labelledby="logout-confirmation-title"
    >
        <div x-on:click.outside="logoutOpen = false" class="w-full max-w-md rounded-2xl border border-white bg-white p-6 text-center shadow-2xl shadow-slate-950/20">
            <p class="text-xs font-bold uppercase tracking-[0.24em] text-blue-600">Konfirmasi Logout</p>
            <h2 id="logout-confirmation-title" class="mt-3 text-2xl font-bold text-slate-950">Yakin ingin keluar?</h2>
            <p class="mt-3 text-sm leading-6 text-slate-600">
                Anda masih login sebagai {{ $user?->name }}. Setelah logout, Anda harus login ulang untuk masuk dashboard.
            </p>

            <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-center">
                <button type="button" x-on:click="logoutOpen = false" class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-blue-200 hover:text-blue-700">
                    Batal
                </button>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-full bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-500">
                        Ya, logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
