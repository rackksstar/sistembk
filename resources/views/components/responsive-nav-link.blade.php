@props(['active'])

@php
$classes = ($active ?? false)
    ? 'block w-full border-l-4 border-blue-500 bg-blue-50 py-2 ps-3 pe-4 text-start text-base font-medium text-blue-700 transition focus:border-blue-600 focus:bg-blue-100 focus:text-blue-800 focus:outline-none dark:border-blue-400 dark:bg-blue-950/40 dark:text-blue-300 dark:focus:border-blue-300 dark:focus:bg-blue-950/60 dark:focus:text-blue-200'
    : 'block w-full border-l-4 border-transparent py-2 ps-3 pe-4 text-start text-base font-medium text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-800 focus:border-slate-300 focus:bg-slate-50 focus:text-slate-800 focus:outline-none dark:text-slate-400 dark:hover:border-slate-600 dark:hover:bg-slate-800 dark:hover:text-slate-200 dark:focus:border-slate-600 dark:focus:bg-slate-800 dark:focus:text-slate-200';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
