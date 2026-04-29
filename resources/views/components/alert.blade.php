@props(['type' => 'success', 'message' => null])

@php
    $styles = [
        'success' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        'error' => 'border-red-200 bg-red-50 text-red-700',
        'info' => 'border-blue-200 bg-blue-50 text-blue-700',
    ][$type] ?? 'border-slate-200 bg-slate-50 text-slate-700';
@endphp

@if($message)
    <div {{ $attributes->merge(['class' => "rounded-2xl border px-4 py-3 text-sm {$styles}"]) }}>
        {{ $message }}
    </div>
@endif
