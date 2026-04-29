@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="overflow-hidden rounded-[2rem] border border-blue-100 bg-[linear-gradient(135deg,#ffffff_0%,#eef5ff_48%,#b7d3ff_100%)] p-6 shadow-sm">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-2xl">
                <p class="inline-flex rounded-full border border-white/80 bg-white/75 px-4 py-2 text-xs font-semibold uppercase tracking-[0.22em] text-blue-700 shadow-sm">Read-only</p>
                <h1 class="mt-5 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">Informasi Karier</h1>
                <p class="mt-3 text-sm leading-7 text-slate-600">Jelajahi referensi karier untuk mengenali minat, peluang studi, dan gambaran pekerjaan masa depan.</p>
            </div>

            <form method="GET" action="{{ route('siswa.careers.index') }}" class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_180px_auto] lg:min-w-[560px]">
                <input name="search" value="{{ $search }}" class="rounded-2xl border border-white/80 bg-white/80 px-4 py-3 text-sm shadow-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="Cari karier..." />
                <select name="category" class="rounded-2xl border border-white/80 bg-white/80 px-4 py-3 text-sm shadow-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
                    <option value="">Semua kategori</option>
                    @foreach($categories as $item)
                        <option value="{{ $item }}" @selected($category === $item)>{{ $item }}</option>
                    @endforeach
                </select>
                <button class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-700">Cari</button>
            </form>
        </div>
    </section>

    <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        @forelse($careers as $career)
            <article class="overflow-hidden rounded-3xl border border-blue-100 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-xl hover:shadow-blue-100">
                <div class="aspect-[16/10] bg-gradient-to-br from-blue-100 via-sky-100 to-white">
                    @if($career->image_path)
                        <img src="{{ asset('storage/'.$career->image_path) }}" alt="{{ $career->title }}" class="h-full w-full object-cover">
                    @else
                        <div class="flex h-full items-center justify-center">
                            <span class="rounded-full bg-white/80 px-4 py-2 text-sm font-semibold text-blue-700 shadow-sm">{{ $career->category }}</span>
                        </div>
                    @endif
                </div>
                <div class="p-5">
                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.14em] text-blue-700">{{ $career->category }}</span>
                    <h2 class="mt-4 text-lg font-semibold text-slate-950">{{ $career->title }}</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-600">{{ $career->description }}</p>
                </div>
            </article>
        @empty
            <div class="md:col-span-2 xl:col-span-3">
                <x-empty-state title="Belum ada informasi karier" description="Informasi karier akan muncul setelah admin menambahkan konten." />
            </div>
        @endforelse
    </section>

    <div>
        {{ $careers->links() }}
    </div>
</div>
@endsection
