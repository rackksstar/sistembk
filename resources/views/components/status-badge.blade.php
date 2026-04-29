@props(['status'])

@php
    $styles = [
        'pending' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'disetujui' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'ditolak' => 'bg-red-50 text-red-700 ring-red-200',
        'selesai' => 'bg-blue-50 text-blue-700 ring-blue-200',
    ][$status] ?? 'bg-slate-50 text-slate-700 ring-slate-200';
@endphp

<span {{ $attributes->merge(['class' => "inline-flex rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.14em] ring-1 {$styles}"]) }}>
    {{ $status }}
</span>
