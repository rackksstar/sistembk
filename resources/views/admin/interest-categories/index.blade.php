@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="modalCrud({{ $errors->any() && old('form_context') !== 'edit' ? 'true' : 'false' }}, {{ $errors->any() && old('form_context') === 'edit' ? (int) old('editing_id') : 'null' }})">
    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-xs">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <x-section-title title="Kategori Minat" description="Kelola kategori minat (RIASEC dan lainnya) untuk asesmen minat bakat." />
            <button type="button" x-on:click="openCreate()" class="w-fit rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-500">Tambah kategori</button>
        </div>

        <x-alert class="mt-5" type="success" :message="session('success')" />
        <x-alert class="mt-5" type="error" :message="session('error')" />

        <form method="GET" action="{{ route('admin.interest-categories.index') }}" class="mt-6 grid gap-3 md:grid-cols-[1fr_auto_auto]">
            <input name="search" value="{{ $search }}" class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm focus:border-blue-400 focus:outline-hidden focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/50" placeholder="Cari kode atau nama kategori..." />
            <button class="rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-500 transition">Cari</button>
            <x-filter-reset />
        </form>

        <div class="mt-6 overflow-hidden rounded-3xl border border-slate-200 dark:border-slate-700">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">
                        <tr>
                            <th class="px-5 py-4">Kode</th>
                            <th class="px-5 py-4">Nama</th>
                            <th class="px-5 py-4">Urutan</th>
                            <th class="px-5 py-4">Soal</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
                        @forelse($categories as $item)
                            <tr>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center gap-2 font-semibold text-slate-900 dark:text-slate-100">
                                        @if($item->warna)
                                            <span class="inline-block h-3 w-3 rounded-full" style="background-color: {{ $item->warna }}"></span>
                                        @endif
                                        {{ $item->kode }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-slate-600 dark:text-slate-400">{{ $item->nama }}</td>
                                <td class="px-5 py-4 text-slate-600 dark:text-slate-400">{{ $item->urutan }}</td>
                                <td class="px-5 py-4 text-slate-600 dark:text-slate-400">{{ $item->questions_count }}</td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $item->is_active ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">
                                        {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <button type="button" x-on:click="openEdit({{ $item->id }})" class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 transition hover:bg-slate-50 dark:hover:bg-slate-800/60">Edit</button>
                                        <form method="POST" action="{{ route('admin.interest-categories.destroy', $item) }}" onsubmit="return confirm('Hapus kategori minat ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="rounded-2xl bg-red-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-red-500">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-6">
                                    <x-empty-state title="Belum ada kategori minat" description="Tambahkan kategori minat untuk mengelompokkan soal asesmen." />
                                    <div class="mt-4 text-center">
                                        <button type="button" x-on:click="createOpen = true" class="rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-500">Tambah kategori</button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-5">{{ $categories->links() }}</div>
    </section>

    <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
        <div x-on:click.outside="createOpen = false" class="w-full max-w-xl max-h-[90vh] overflow-y-auto rounded-3xl bg-white dark:bg-slate-900 p-6 shadow-2xl">
            <x-section-title title="Tambah Kategori Minat" description="Isi kode singkat dan nama kategori minat." />
            <form method="POST" action="{{ route('admin.interest-categories.store') }}" class="mt-6 space-y-4">
                @csrf
                <input type="hidden" name="form_context" value="create">
                @include('admin.interest-categories.partials.form', ['category' => null, 'submit' => 'Simpan kategori'])
            </form>
        </div>
    </div>

    @foreach($categories as $item)
        <div x-show="editOpen === {{ $item->id }}" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
            <div x-on:click.outside="editOpen = null" class="w-full max-w-xl max-h-[90vh] overflow-y-auto rounded-3xl bg-white dark:bg-slate-900 p-6 shadow-2xl">
                <x-section-title title="Edit Kategori Minat" description="Perbarui data kategori minat." />
                <form method="POST" action="{{ route('admin.interest-categories.update', $item) }}" class="mt-6 space-y-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="form_context" value="edit">
                    <input type="hidden" name="editing_id" value="{{ $item->id }}">
                    @include('admin.interest-categories.partials.form', ['category' => $item, 'submit' => 'Update kategori'])
                </form>
            </div>
        </div>
    @endforeach
</div>
@endsection
