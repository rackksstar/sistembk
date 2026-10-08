@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="modalCrud({{ $errors->any() && old('form_context') !== 'edit' ? 'true' : 'false' }}, {{ $errors->any() && old('form_context') === 'edit' ? (int) old('editing_id') : 'null' }})">
    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-xs">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <x-section-title title="Program Studi PCR" description="Kelola program studi dan relasi kategori minat untuk rekomendasi SMA." />
            <button type="button" x-on:click="openCreate()" class="w-fit rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-500">Tambah program studi</button>
        </div>

        <x-alert class="mt-5" type="success" :message="session('success')" />
        <x-alert class="mt-5" type="error" :message="session('error')" />

        <form method="GET" action="{{ route('admin.program-studi.index') }}" class="mt-6 grid gap-3 lg:grid-cols-[minmax(0,1fr)_180px_160px_140px_auto_auto]">
            <input name="search" value="{{ $search }}" class="ui-input js-select2" placeholder="Cari nama, institusi, atau jurusan..." />
            <select name="jurusan" class="ui-input js-select2" data-placeholder="Semua jurusan">
                <option value="">Semua jurusan</option>
                @foreach($jurusanOptions as $item)
                    <option value="{{ $item }}" @selected($jurusan === $item)>{{ $item }}</option>
                @endforeach
            </select>
            <select name="verified" class="ui-input js-select2" data-placeholder="Semua verifikasi">
                <option value="">Semua verifikasi</option>
                <option value="1" @selected($verified === '1')>Terverifikasi</option>
                <option value="0" @selected($verified === '0')>Belum</option>
            </select>
            <select name="active" class="ui-input js-select2" data-placeholder="Semua status">
                <option value="">Semua status</option>
                <option value="1" @selected($active === '1')>Aktif</option>
                <option value="0" @selected($active === '0')>Nonaktif</option>
            </select>
            <button class="rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-500 transition">Terapkan</button>
            <x-filter-reset />
        </form>

        <div class="mt-6 overflow-hidden rounded-3xl border border-slate-200 dark:border-slate-700">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">
                        <tr>
                            <th class="px-5 py-4">Program Studi</th>
                            <th class="px-5 py-4">Jenjang</th>
                            <th class="px-5 py-4">Jurusan</th>
                            <th class="px-5 py-4">Tag Minat</th>
                            <th class="px-5 py-4">Verifikasi</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
                        @forelse($programStudis as $item)
                            <tr>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-slate-900 dark:text-slate-100">{{ $item->nama }}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">{{ $item->institusi }}</div>
                                </td>
                                <td class="px-5 py-4 text-slate-600 dark:text-slate-400">{{ $item->jenjang_pendidikan }}</td>
                                <td class="px-5 py-4 text-slate-600 dark:text-slate-400">{{ $item->jurusan ?? '-' }}</td>
                                <td class="px-5 py-4 text-slate-600 dark:text-slate-400">{{ $item->kode_tag ?: '-' }}</td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $item->is_verified ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300' : 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300' }}">
                                        {{ $item->is_verified ? 'Terverifikasi' : 'Belum' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $item->is_active ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">
                                        {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <button type="button" x-on:click="openEdit({{ $item->id }})" class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 transition hover:bg-slate-50 dark:hover:bg-slate-800/60">Edit</button>
                                        <form method="POST" action="{{ route('admin.program-studi.destroy', $item) }}" onsubmit="return confirm('Hapus program studi ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="rounded-2xl bg-red-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-red-500">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-6">
                                    <x-empty-state title="Belum ada program studi" description="Tambahkan program studi PCR untuk rekomendasi siswa SMA." />
                                    <div class="mt-4 text-center">
                                        <button type="button" x-on:click="createOpen = true" class="rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-500">Tambah program studi</button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-5">{{ $programStudis->links() }}</div>
    </section>

    <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
        <div x-on:click.outside="createOpen = false" class="w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-3xl bg-white dark:bg-slate-900 p-6 shadow-2xl">
            <x-section-title title="Tambah Program Studi" description="Lengkapi data prodi dan tautkan kategori minat." />
            <form method="POST" action="{{ route('admin.program-studi.store') }}" class="mt-6 space-y-4">
                @csrf
                <input type="hidden" name="form_context" value="create">
                @include('admin.program-studi.partials.form', ['programStudi' => null, 'interestCategories' => $interestCategories, 'submit' => 'Simpan program studi'])
            </form>
        </div>
    </div>

    @foreach($programStudis as $item)
        <div x-show="editOpen === {{ $item->id }}" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
            <div x-on:click.outside="editOpen = null" class="w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-3xl bg-white dark:bg-slate-900 p-6 shadow-2xl">
                <x-section-title title="Edit Program Studi" description="Perbarui data prodi dan relasi kategori minat." />
                <form method="POST" action="{{ route('admin.program-studi.update', $item) }}" class="mt-6 space-y-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="form_context" value="edit">
                    <input type="hidden" name="editing_id" value="{{ $item->id }}">
                    @include('admin.program-studi.partials.form', ['programStudi' => $item, 'interestCategories' => $interestCategories, 'submit' => 'Update program studi'])
                </form>
            </div>
        </div>
    @endforeach
</div>
@endsection
