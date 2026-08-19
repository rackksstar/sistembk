<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Dashboard sistem bimbingan konseling untuk admin, guru BK, dan siswa.">
    <title>{{ config('app.name', 'BK System') }} | Dashboard</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v=bk-system">
    <link rel="shortcut icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v=bk-system">
    @include('partials.theme-init')
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body
    class="h-full text-slate-900 dark:text-slate-100"
    x-data="{
        sidebarOpen: false,
        logoutOpen: false,
        init() {
            this.$watch('sidebarOpen', open => {
                document.body.classList.toggle('overflow-hidden', open);
            });
        },
    }"
    x-on:close-sidebar.window="sidebarOpen = false"
    x-on:open-logout.window="logoutOpen = true"
    x-on:keydown.escape.window="sidebarOpen = false; logoutOpen = false"
>
    @php
        $user = auth()->user();
        $roleLabel = ['admin' => 'Admin', 'guru' => 'Guru BK', 'siswa' => 'Siswa'][$user?->role] ?? 'Pengguna';
        $userIdentity = $user?->role === 'guru' ? ($user?->username ?? '-') : ($user?->email ?? '-');
        $dashboardRoute = $user ? route($user->dashboardRoute()) : route('login');
        $menuGroups = $user && isset(config('navigation')[$user->role])
            ? config('navigation')[$user->role]
            : [];

        $activeGroup = collect($menuGroups)->first(function ($group) {
            return collect($group['items'] ?? [])->contains(function ($item) {
                $patterns = (array) ($item['active'] ?? $item['route'] ?? '');

                return collect($patterns)->contains(fn ($pattern) => request()->routeIs($pattern));
            });
        });

        $activeItem = collect($activeGroup['items'] ?? [])
            ->first(function ($item) {
                $patterns = (array) ($item['active'] ?? $item['route'] ?? '');

                return collect($patterns)->contains(fn ($pattern) => request()->routeIs($pattern));
            });

        $pageTitle = request()->routeIs('profile.*')
            ? 'Profil'
            : ($activeItem['title'] ?? $activeItem['label'] ?? 'Dashboard');

        $pageCrumb = request()->routeIs('profile.*')
            ? 'Akun'
            : ($activeGroup['group'] ?? $roleLabel);
    @endphp

    <div
        x-cloak
        x-show="sidebarOpen"
        x-transition.opacity
        class="app-sidebar-overlay lg:hidden"
        x-on:click="sidebarOpen = false"
        aria-hidden="true"
    ></div>

    <x-app-sidebar
        :menu-groups="$menuGroups"
        :dashboard-route="$dashboardRoute"
        :role-label="$roleLabel"
        :user="$user"
        :user-identity="$userIdentity"
        x-bind:class="{ 'app-sidebar--open': sidebarOpen }"
    />

    <div class="app-layout">
        <header class="app-topbar">
            <div class="app-topbar__inner">
                <div class="flex min-w-0 items-center gap-3">
                    <button
                        type="button"
                        class="app-topbar__menu-btn lg:hidden"
                        x-on:click="sidebarOpen = true"
                        aria-label="Buka menu"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <div class="min-w-0">
                        <nav class="app-topbar__crumb" aria-label="Breadcrumb">
                            <span>{{ $roleLabel }}</span>
                            <span class="app-topbar__crumb-sep" aria-hidden="true">/</span>
                            <span>{{ $pageCrumb }}</span>
                        </nav>
                        <h1 class="app-topbar__title">{{ $pageTitle }}</h1>
                    </div>
                </div>

                <div class="flex items-center gap-2 sm:gap-3">
                    <x-theme-toggle />
                    <span class="hidden text-right sm:block">
                        <span class="app-topbar__user-name">{{ $user?->name }}</span>
                        <span class="app-topbar__user-meta">{{ $userIdentity }}</span>
                    </span>
                    <button
                        type="button"
                        class="app-topbar__logout"
                        x-on:click="logoutOpen = true"
                    >
                        Keluar
                    </button>
                </div>
            </div>
        </header>

        <main class="app-main">
            @isset($header)
                <div class="ui-panel mb-6">
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

    <div
        x-cloak
        x-show="logoutOpen"
        x-transition.opacity
        class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/60 px-4 backdrop-blur-sm dark:bg-slate-950/80"
        role="dialog"
        aria-modal="true"
        aria-labelledby="logout-confirmation-title"
    >
        <div x-on:click.outside="logoutOpen = false" class="ui-modal shadow-2xl shadow-slate-950/20 dark:shadow-black/40">
            <p class="text-xs font-bold uppercase tracking-[0.24em] text-blue-600 dark:text-blue-400">Konfirmasi Logout</p>
            <h2 id="logout-confirmation-title" class="mt-3 text-2xl font-bold text-slate-950 dark:text-white">Yakin ingin keluar?</h2>
            <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-400">
                Anda masih login sebagai {{ $user?->name }}. Setelah logout, Anda harus login ulang untuk masuk dashboard.
            </p>

            <div class="mt-6 flex flex-col items-center justify-center gap-3 sm:flex-row">
                <button type="button" x-on:click="logoutOpen = false" class="ui-btn-secondary rounded-full px-5 py-3">
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
