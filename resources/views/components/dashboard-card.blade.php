@props(['title', 'description', 'value' => null, 'href' => null, 'icon' => null, 'color' => 'from-slate-500 to-slate-700'])

<div class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg focus-within:ring-2 focus-within:ring-sky-500/40">
    <div class="p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h3 class="text-base font-semibold text-slate-900">{{ $title }}</h3>
                @if($value !== null)
                    <p class="mt-4 text-3xl font-semibold tracking-tight text-slate-900">{{ $value }}</p>
                @endif
                <p class="mt-3 text-sm leading-6 text-slate-600">{{ $description }}</p>
            </div>
            <div class="rounded-2xl bg-gradient-to-br p-3 text-white {{ $color }}">
                @if($icon)
                    {!! $icon !!}
                @else
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6">
                        <path d="M4 19V5"/>
                        <path d="M4 19h16"/>
                        <path d="M8 16v-5"/>
                        <path d="M12 16V8"/>
                        <path d="M16 16v-3"/>
                    </svg>
                @endif
            </div>
        </div>
    </div>
    @if($href)
        <div class="border-t border-slate-100 bg-slate-50 px-6 py-4 text-sm">
            <a href="{{ $href }}" class="font-medium text-sky-600 hover:text-sky-700">Lihat detail<span aria-hidden="true"> -></span></a>
        </div>
    @endif
</div>
