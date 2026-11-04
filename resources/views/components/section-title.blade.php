@props(['title', 'description' => null])

<div class="space-y-2">
    <h2 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ $title }}</h2>
    @if($description)
        <p class="text-sm leading-6 text-slate-600 dark:text-slate-400">{{ $description }}</p>
    @endif
</div>
