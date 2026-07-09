@extends('layouts.app')

@section('content')
<article class="space-y-6">
    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
        <a href="{{ route('siswa.postingan.index') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-500">← Kembali ke daftar artikel</a>

        <div class="mt-4">
            <span class="rounded-full bg-blue-50 dark:bg-blue-950/40 px-3 py-1 text-xs font-semibold text-blue-700 dark:text-blue-300">{{ $postingan->kategori?->name }}</span>
            <h1 class="mt-4 text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ $postingan->judul }}</h1>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">{{ $postingan->created_at?->format('d M Y') }}</p>
        </div>

        @if($postingan->gambar_path)
            <img src="{{ asset('storage/'.$postingan->gambar_path) }}" alt="{{ $postingan->judul }}" class="mt-6 w-full max-h-96 rounded-2xl object-cover">
        @endif

        <div class="prose prose-slate mt-6 max-w-none text-sm leading-relaxed text-slate-700 dark:text-slate-300">
            {!! nl2br(e($postingan->isi)) !!}
        </div>
    </section>
</article>
@endsection
