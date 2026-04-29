@props(['title', 'description' => null])

<div {{ $attributes->merge(['class' => 'rounded-3xl border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center']) }}>
    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-blue-600 shadow-sm">
        <span class="text-lg font-semibold">BK</span>
    </div>
    <h3 class="mt-4 text-base font-semibold text-slate-900">{{ $title }}</h3>
    @if($description)
        <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-600">{{ $description }}</p>
    @endif
</div>
