@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
        <x-section-title title="Artikel BK" description="Baca informasi dan artikel bimbingan konseling." />

        <form method="GET" class="mt-6 grid gap-3 md:grid-cols-[1fr_200px_auto]">
            <input name="search" value="{{ $search }}" placeholder="Cari artikel..." class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-sm">
            <select name="kategori" class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-sm">
                <option value="">Semua kategori</option>
                @foreach($kategoris as $kat)
                    <option value="{{ $kat->id }}" @selected((int) $kategoriId === $kat->id)>{{ $kat->name }}</option>
                @endforeach
            </select>
            <button class="rounded-2xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-500">Cari</button>
        </form>
    </section>

    <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        @forelse($postingan as $item)
            <article class="overflow-hidden rounded-3xl border border-blue-100 dark:border-blue-900/50 bg-white dark:bg-slate-900 shadow-sm">
                <div class="aspect-[16/10] bg-gradient-to-br from-blue-50 to-sky-100 dark:from-slate-800 dark:to-slate-900">
                    @if($item->gambar_path)
                        <img src="{{ asset('storage/'.$item->gambar_path) }}" alt="{{ $item->judul }}" class="h-full w-full object-cover">
                    @endif
                </div>
                <div class="space-y-3 p-5">
                    <span class="rounded-full bg-blue-50 dark:bg-blue-950/40 px-3 py-1 text-xs font-semibold text-blue-700 dark:text-blue-300">{{ $item->kategori?->name }}</span>
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ $item->judul }}</h2>
                    <p class="line-clamp-3 text-sm text-slate-600 dark:text-slate-400">{{ Str::limit(strip_tags($item->isi), 120) }}</p>
                    <a href="{{ route('siswa.postingan.show', $item) }}" class="inline-flex rounded-2xl bg-blue-600 px-4 py-2 text-xs font-semibold text-white hover:bg-blue-500">Baca selengkapnya</a>
                </div>
            </article>
        @empty
            <div class="md:col-span-2 xl:col-span-3">
                <x-empty-state title="Belum ada artikel" description="Artikel akan muncul setelah admin mempublikasikan konten." />
            </div>
        @endforelse
    </section>

    <div class="mt-5">{{ $postingan->links() }}</div>
</div>
@endsection
