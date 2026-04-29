@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ createOpen: {{ $errors->any() ? 'true' : 'false' }}, editOpen: null }">
    <section class="overflow-hidden rounded-3xl border border-blue-100 bg-[linear-gradient(135deg,#f9fbff_0%,#eef5ff_45%,#cfe2ff_100%)] p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <x-section-title
                title="Informasi Karier"
                description="Kelola konten karier untuk membantu siswa mengenali pilihan studi dan pekerjaan."
            />
            <button type="button" x-on:click="createOpen = true" class="w-fit rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-500">Tambah karier</button>
        </div>

        <x-alert class="mt-5" type="success" :message="session('success')" />

        <form method="GET" action="{{ route('admin.careers.index') }}" class="mt-6 grid gap-3 md:grid-cols-[minmax(0,1fr)_220px_auto]">
            <input name="search" value="{{ $search }}" class="rounded-2xl border border-white/80 bg-white/80 px-4 py-3 text-sm shadow-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="Cari judul atau deskripsi..." />
            <select name="category" class="rounded-2xl border border-white/80 bg-white/80 px-4 py-3 text-sm shadow-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
                <option value="">Semua kategori</option>
                @foreach($categories as $item)
                    <option value="{{ $item }}" @selected($category === $item)>{{ $item }}</option>
                @endforeach
            </select>
            <button class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-700">Terapkan</button>
        </form>
    </section>

    <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        @forelse($careers as $career)
            <article class="overflow-hidden rounded-3xl border border-blue-100 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-xl hover:shadow-blue-100">
                <div class="aspect-[16/10] bg-gradient-to-br from-blue-100 via-sky-100 to-white">
                    @if($career->image_path)
                        <img src="{{ asset('storage/'.$career->image_path) }}" alt="{{ $career->title }}" class="h-full w-full object-cover">
                    @else
                        <div class="flex h-full items-center justify-center text-blue-600">
                            <span class="rounded-full bg-white/80 px-4 py-2 text-sm font-semibold shadow-sm">{{ $career->category }}</span>
                        </div>
                    @endif
                </div>
                <div class="space-y-4 p-5">
                    <div>
                        <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.14em] text-blue-700">{{ $career->category }}</span>
                        <h3 class="mt-4 text-lg font-semibold text-slate-950">{{ $career->title }}</h3>
                        <p class="mt-2 line-clamp-3 text-sm leading-6 text-slate-600">{{ $career->description }}</p>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" x-on:click="editOpen = {{ $career->id }}" class="flex-1 rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Edit</button>
                        <form method="POST" action="{{ route('admin.careers.destroy', $career) }}" class="flex-1" onsubmit="return confirm('Hapus informasi karier ini?')">
                            @csrf
                            @method('DELETE')
                            <button class="w-full rounded-2xl bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-500">Delete</button>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <div class="md:col-span-2 xl:col-span-3">
                <x-empty-state title="Belum ada informasi karier" description="Tambahkan konten pertama agar siswa bisa mulai membaca referensi karier." />
            </div>
        @endforelse
    </section>

    <div>
        {{ $careers->links() }}
    </div>

    <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
        <div x-on:click.outside="createOpen = false" class="max-h-[90vh] w-full max-w-xl overflow-y-auto rounded-3xl bg-white p-6 shadow-2xl">
            <div class="flex items-start justify-between gap-4">
                <x-section-title title="Tambah Informasi Karier" description="Buat card karier baru untuk siswa." />
                <button type="button" x-on:click="createOpen = false" class="rounded-full bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-600">x</button>
            </div>
            <form method="POST" action="{{ route('admin.careers.store') }}" enctype="multipart/form-data" class="mt-6 space-y-4">
                @csrf
                @include('admin.careers.partials.form', ['career' => null, 'submit' => 'Simpan karier'])
            </form>
        </div>
    </div>

    @foreach($careers as $career)
        <div x-show="editOpen === {{ $career->id }}" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
            <div x-on:click.outside="editOpen = null" class="max-h-[90vh] w-full max-w-xl overflow-y-auto rounded-3xl bg-white p-6 shadow-2xl">
                <div class="flex items-start justify-between gap-4">
                    <x-section-title title="Edit Informasi Karier" description="Perbarui card karier siswa." />
                    <button type="button" x-on:click="editOpen = null" class="rounded-full bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-600">x</button>
                </div>
                <form method="POST" action="{{ route('admin.careers.update', $career) }}" enctype="multipart/form-data" class="mt-6 space-y-4">
                    @csrf
                    @method('PUT')
                    @include('admin.careers.partials.form', ['career' => $career, 'submit' => 'Update karier'])
                </form>
            </div>
        </div>
    @endforeach
</div>
@endsection
