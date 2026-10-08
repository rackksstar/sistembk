@php
    $roleConfig = [
        'login' => [
            'label' => 'Portal Akses',
            'accent' => 'from-blue-600 to-sky-400',
        ],
        'siswa' => [
            'label' => 'Akun Siswa',
            'accent' => 'from-blue-600 to-indigo-400',
        ],
        'guru' => [
            'label' => 'Akun Guru BK',
            'accent' => 'from-sky-500 to-blue-600',
        ],
    ][$role] ?? [
        'label' => 'BK System',
        'accent' => 'from-blue-600 to-sky-400',
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Sistem Informasi BK') }}</title>
    @include('partials.theme-init')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-slate-50 dark:bg-slate-800/60 text-slate-900 dark:text-slate-100 antialiased dark:bg-slate-950 dark:text-slate-100">
    <div class="relative min-h-screen overflow-hidden bg-[linear-gradient(135deg,#f8fbff_0%,#edf5ff_52%,#dbe7f5_100%)] dark:bg-gradient-to-br dark:from-slate-950 dark:via-slate-900 dark:to-slate-950">
        <div class="absolute left-[-120px] top-[-120px] h-80 w-80 rounded-full bg-blue-200/40 blur-3xl dark:bg-blue-900/20"></div>
        <div class="absolute bottom-[-140px] right-[-120px] h-96 w-96 rounded-full bg-sky-300/30 blur-3xl dark:bg-sky-900/15"></div>

        <header class="relative z-10 px-4 py-6 sm:px-6">
            <nav class="mx-auto flex max-w-6xl items-center justify-between rounded-full border border-white/80 dark:border-slate-700/80 bg-white/80 dark:bg-slate-900/80 px-4 py-3 shadow-md shadow-blue-100/70 backdrop-blur dark:border-slate-700/80 dark:bg-slate-900/80 dark:shadow-black/20 sm:px-6">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-3">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-blue-600 text-sm font-bold text-white shadow-lg shadow-blue-500/25">BK</span>
                    <span class="text-base font-semibold tracking-wide text-slate-950 dark:text-white">BK System</span>
                </a>

                <div class="flex items-center gap-2 text-sm">
                    <x-theme-toggle />
                    @if($role === 'login')
                        <span class="hidden rounded-full bg-slate-100 dark:bg-slate-800 px-3 py-2 text-xs font-semibold text-slate-500 dark:bg-slate-800 dark:text-slate-400 sm:inline-flex">Admin</span>
                        <a href="{{ route('login') }}" class="rounded-full bg-slate-100 dark:bg-slate-800 px-4 py-2 font-semibold text-slate-700 dark:text-slate-300 transition hover:bg-slate-200 dark:hover:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">Masuk</a>
                    @endif
                </div>
            </nav>
        </header>

        <main class="relative z-10 flex min-h-[calc(100vh-112px)] items-center justify-center px-4 pb-10 sm:px-6">
            <div class="w-full max-w-lg">
                <div class="mb-5 text-center">
                    <span class="inline-flex rounded-full border border-white/80 dark:border-slate-700/80 bg-white/80 dark:bg-slate-900/80 px-4 py-2 text-xs font-bold uppercase tracking-[0.22em] text-blue-600 shadow-sm dark:border-slate-700/80 dark:bg-slate-900/80 dark:text-blue-400">
                        {{ $roleConfig['label'] }}
                    </span>
                </div>

                <div class="overflow-hidden rounded-2xl border border-white dark:border-slate-700 bg-white dark:bg-slate-900 shadow-lg shadow-blue-200/60 dark:border-slate-700 dark:bg-slate-900 dark:shadow-black/30">
                    <div class="h-2 bg-gradient-to-r {{ $roleConfig['accent'] }}"></div>
                    <div class="p-6 sm:p-8">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
