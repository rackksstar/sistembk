@props([
    'title',
    'description',
    'href',
    'cta',
    'accent' => 'bg-blue-600',
])

<a
    href="{{ $href }}"
    {{ $attributes->merge(['class' => 'ui-card group flex flex-col justify-between p-6 transition hover:-translate-y-0.5 hover:shadow-md']) }}
>
    <div>
        <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl {{ $accent }} text-white shadow-sm">
            <span class="h-2.5 w-2.5 rounded-full bg-white/90"></span>
        </span>
        <h3 class="mt-6 text-lg font-bold tracking-tight text-slate-900 dark:text-slate-100">{{ $title }}</h3>
        <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-400">{{ $description }}</p>
    </div>
    <div class="mt-6">
        <span class="inline-flex items-center rounded-full bg-slate-900 px-4 py-2 text-xs font-semibold text-white transition group-hover:bg-blue-600 dark:bg-slate-700 dark:group-hover:bg-blue-600">
            {{ $cta }}
        </span>
    </div>
</a>
