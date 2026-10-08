@props(['active'])

@php
$classes = ($active ?? false)
    ? 'inline-flex items-center border-b-2 border-blue-500 px-1 pt-1 text-sm font-medium leading-5 text-slate-900 transition focus:outline-none focus:border-blue-600 dark:border-blue-400 dark:text-slate-100'
    : 'inline-flex items-center border-b-2 border-transparent px-1 pt-1 text-sm font-medium leading-5 text-slate-500 transition hover:border-slate-300 hover:text-slate-700 focus:outline-none focus:border-slate-300 focus:text-slate-700 dark:text-slate-400 dark:hover:border-slate-600 dark:hover:text-slate-200 dark:focus:border-slate-600 dark:focus:text-slate-200';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
