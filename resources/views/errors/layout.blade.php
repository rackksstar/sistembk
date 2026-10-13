<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error {{ $errorCode }} - {{ $errorTitle }} | {{ config('app.name', 'BK System') }}</title>
    @include('partials.theme-init')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-full items-center justify-center bg-slate-50 px-4 py-12 text-slate-900 dark:bg-slate-950 dark:text-slate-100">
    @php
        $user = auth()->user();
        $fallbackUrl = $user ? route($user->dashboardRoute()) : route('login');
    @endphp

    <main class="w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-lg shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20 sm:p-10">
        <p class="text-6xl font-extrabold tracking-tight text-blue-600 dark:text-blue-400">{{ $errorCode }}</p>
        <p class="mt-4 text-sm font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Terjadi Kendala</p>
        <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-950 dark:text-white">{{ $errorTitle }}</h1>
        <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-400">{{ $errorMessage }}</p>

        <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
            <button
                type="button"
                onclick="if (window.history.length > 1) { window.history.back(); } else { window.location.href = @js($fallbackUrl); }"
                class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
            >
                Kembali
            </button>
            <a href="{{ $actionUrl }}" class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-500">
                {{ $actionLabel }}
            </a>
        </div>
    </main>
</body>
</html>
