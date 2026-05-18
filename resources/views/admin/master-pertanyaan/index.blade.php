@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ createOpen: {{ $errors->any() ? 'true' : 'false' }}, editOpen: null }">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <x-section-title title="Master Pertanyaan" description="Kelola pertanyaan aktif untuk angket dan tryout." />
            <button type="button" x-on:click="createOpen = true" class="w-fit rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-500">Tambah pertanyaan</button>
        </div>

        <x-alert class="mt-5" type="success" :message="session('success')" />
        <x-alert class="mt-5" type="error" :message="session('error')" />

        <form method="GET" action="{{ route('admin.master-pertanyaan.index') }}" class="mt-6 grid gap-3 lg:grid-cols-[minmax(0,1fr)_180px_180px_auto]">
            <input name="search" value="{{ $search }}" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="Cari teks pertanyaan..." />
            <select name="kategori" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
                <option value="">Semua kategori</option>
                @foreach($kategoriOptions as $item)
                    <option value="{{ $item }}" @selected($kategori === $item)>{{ strtoupper($item) }}</option>
                @endforeach
            </select>
            <select name="active" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
                <option value="">Semua status</option>
                <option value="1" @selected($active === '1')>Aktif</option>
                <option value="0" @selected($active === '0')>Nonaktif</option>
            </select>
            <button class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-700">Terapkan</button>
        </form>

        <div class="mt-6 overflow-hidden rounded-3xl border border-slate-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                        <tr>
                            <th class="px-5 py-4">Kategori</th>
                            <th class="px-5 py-4">Tipe</th>
                            <th class="px-5 py-4">Pertanyaan</th>
                            <th class="px-5 py-4">Aktif</th>
                            <th class="px-5 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($questions as $q)
                            <tr>
                                <td class="px-5 py-4 text-slate-700">{{ strtoupper($q->kategori) }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ str_replace('_', ' ', $q->tipe_input) }}</td>
                                <td class="px-5 py-4 font-semibold text-slate-900">{{ $q->teks_pertanyaan }}</td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $q->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $q->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <button type="button" x-on:click="editOpen = {{ $q->id }}" class="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">Edit</button>
                                        <form method="POST" action="{{ route('admin.master-pertanyaan.destroy', $q) }}" onsubmit="return confirm('Hapus pertanyaan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="rounded-2xl bg-red-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-red-500">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-6">
                                    <x-empty-state title="Belum ada pertanyaan" description="Tambahkan pertanyaan untuk kebutuhan angket dan tryout." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-5">{{ $questions->links() }}</div>
    </section>

    <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
        <div x-on:click.outside="createOpen = false" class="w-full max-w-xl rounded-3xl bg-white p-6 shadow-2xl">
            <x-section-title title="Tambah Pertanyaan" description="Isi kategori, tipe input, dan teks pertanyaan." />
            <form method="POST" action="{{ route('admin.master-pertanyaan.store') }}" class="mt-6 space-y-4">
                @csrf
                @include('admin.master-pertanyaan.partials.form', ['question' => null, 'kategoriOptions' => $kategoriOptions, 'tipeOptions' => $tipeOptions, 'submit' => 'Simpan pertanyaan'])
            </form>
        </div>
    </div>

    @foreach($questions as $q)
        <div x-show="editOpen === {{ $q->id }}" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
            <div x-on:click.outside="editOpen = null" class="w-full max-w-xl rounded-3xl bg-white p-6 shadow-2xl">
                <x-section-title title="Edit Pertanyaan" description="Perbarui pertanyaan yang sudah ada." />
                <form method="POST" action="{{ route('admin.master-pertanyaan.update', $q) }}" class="mt-6 space-y-4">
                    @csrf
                    @method('PUT')
                    @include('admin.master-pertanyaan.partials.form', ['question' => $q, 'kategoriOptions' => $kategoriOptions, 'tipeOptions' => $tipeOptions, 'submit' => 'Update pertanyaan'])
                </form>
            </div>
        </div>
    @endforeach
</div>
@endsection

