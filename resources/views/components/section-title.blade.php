@props(['title', 'description' => null])

<div class="space-y-2">
    <h2 class="text-2xl font-semibold text-slate-900">{{ $title }}</h2>
    @if($description)
        <p class="text-sm leading-6 text-slate-600">{{ $description }}</p>
    @endif
</div>
