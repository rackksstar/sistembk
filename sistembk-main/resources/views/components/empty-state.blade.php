@props(['title', 'description' => null])

<div {{ $attributes->merge(['class' => 'rounded-3xl border border-dashed border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-6 py-10 text-center dark:border-slate-600 dark:bg-slate-800/40']) }}>
    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-white dark:bg-slate-900 text-blue-600 shadow-sm dark:bg-slate-900 dark:text-blue-400">
        <span class="text-lg font-semibold">BK</span>
    </div>
    <h3 class="mt-4 text-base font-semibold text-slate-900 dark:text-slate-100">{{ $title }}</h3>
    @if($description)
        <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-600 dark:text-slate-400">{{ $description }}</p>
    @endif
    @if(isset($slot) && ! $slot->isEmpty())
        <div class="mt-4">{{ $slot }}</div>
    @endif
</div>
