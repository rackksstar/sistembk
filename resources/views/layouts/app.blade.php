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
        $userIdentity = $user?->role === 'guru' ? ($user?->username ?? '-') : ($user?->email ?? '-');
        $dashboardRoute = $user ? route($user->dashboardRoute()) : route('login');
        $menuGroups = $user && isset(config('navigation')[$user->role])
            ? config('navigation')[$user->role]
            : [];
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
                        <p class="text-xs text-slate-500">{{ $userIdentity }}</p>
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
                <nav class="space-y-1" aria-label="Menu utama">
                    @foreach($menuGroups as $group)
                        <p class="px-4 pb-1 pt-4 text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400 first:pt-0">{{ $group['group'] }}</p>
                        @foreach($group['items'] as $item)
                            @php
                                $patterns = (array) ($item['active'] ?? $item['route'] ?? '');
                                $isActive = collect($patterns)->contains(fn ($pattern) => request()->routeIs($pattern));
                                $href = isset($item['route']) ? route($item['route']) : $dashboardRoute;
                            @endphp
                            <a href="{{ $href }}" class="flex items-center justify-between rounded-2xl px-4 py-2.5 text-sm font-semibold transition {{ $isActive ? 'bg-blue-50 text-blue-700 ring-1 ring-blue-100' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                <span>{{ $item['label'] }}</span>
                                @if($isActive)
                                    <span class="h-2 w-2 rounded-full bg-blue-600"></span>
                                @endif
                            </a>
                        @endforeach
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

            <div class="mt-6 flex flex-col items-center justify-center gap-3 sm:flex-row">
                <button type="button" x-on:click="logoutOpen = false" class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-blue-200 hover:text-blue-700">
                    Batal
                </button>
                <form method="POST" action="{{ route('logout') }}" class="inline-flex justify-center">
                    @csrf
                    <button type="submit" class="inline-flex items-center justify-center rounded-full bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-500">
                        Ya, logout
                    </button>
                </form>
            </div>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
