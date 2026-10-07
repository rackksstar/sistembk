@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="modalCrud({{ $errors->any() && old('form_context') !== 'edit' ? 'true' : 'false' }}, {{ $errors->any() && old('form_context') === 'edit' ? (int) old('editing_id') : 'null' }})">
    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <x-section-title title="Bidang Karier" description="Kelola bidang karier dan relasi kategori minat untuk rekomendasi SMK." />
            <button type="button" x-on:click="openCreate()" class="w-fit rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-500">Tambah bidang karier</button>
        </div>

        <x-alert class="mt-5" type="success" :message="session('success')" />
        <x-alert class="mt-5" type="error" :message="session('error')" />

        <form method="GET" action="{{ route('admin.bidang-karier.index') }}" class="mt-6 grid gap-3 md:grid-cols-[1fr_160px_auto_auto]">
            <input name="search" value="{{ $search }}" class="ui-input js-select2" placeholder="Cari nama atau deskripsi..." />
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
                            <th class="px-5 py-4">Bidang Karier</th>
                            <th class="px-5 py-4">Job Zone</th>
                            <th class="px-5 py-4">Tag Minat</th>
                            <th class="px-5 py-4">Contoh Pekerjaan</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
                        @forelse($careerFields as $item)
                            <tr>
                                <td class="px-5 py-4 font-semibold text-slate-900 dark:text-slate-100">{{ $item->nama }}</td>
                                <td class="px-5 py-4 text-slate-600 dark:text-slate-400">{{ $item->job_zone ? 'Zone '.$item->job_zone : '-' }}</td>
                                <td class="px-5 py-4 text-slate-600 dark:text-slate-400">{{ $item->kode_tag ?: '-' }}</td>
                                <td class="px-5 py-4 text-slate-600 dark:text-slate-400">
                                    @php $jobs = $item->contoh_pekerjaan ?? []; @endphp
                                    {{ count($jobs) ? implode(', ', array_slice($jobs, 0, 2)).(count($jobs) > 2 ? '…' : '') : '-' }}
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $item->is_active ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">
                                        {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <button type="button" x-on:click="openEdit({{ $item->id }})" class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 transition hover:bg-slate-50 dark:hover:bg-slate-800/60">Edit</button>
                                        <form method="POST" action="{{ route('admin.bidang-karier.destroy', $item) }}" onsubmit="return confirm('Hapus bidang karier ini?')">
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
                                    <x-empty-state title="Belum ada bidang karier" description="Tambahkan bidang karier untuk rekomendasi siswa SMK." />
                                    <div class="mt-4 text-center">
                                        <button type="button" x-on:click="createOpen = true" class="rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-500">Tambah bidang karier</button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-5">{{ $careerFields->links() }}</div>
    </section>

    <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
        <div x-on:click.outside="createOpen = false" class="w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-3xl bg-white dark:bg-slate-900 p-6 shadow-2xl">
            <x-section-title title="Tambah Bidang Karier" description="Lengkapi data bidang karier dan tautkan kategori minat." />
            <form method="POST" action="{{ route('admin.bidang-karier.store') }}" class="mt-6 space-y-4">
                @csrf
                <input type="hidden" name="form_context" value="create">
                @include('admin.bidang-karier.partials.form', ['careerField' => null, 'interestCategories' => $interestCategories, 'submit' => 'Simpan bidang karier'])
            </form>
        </div>
    </div>

    @foreach($careerFields as $item)
        <div x-show="editOpen === {{ $item->id }}" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
            <div x-on:click.outside="editOpen = null" class="w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-3xl bg-white dark:bg-slate-900 p-6 shadow-2xl">
                <x-section-title title="Edit Bidang Karier" description="Perbarui data bidang karier dan relasi kategori minat." />
                <form method="POST" action="{{ route('admin.bidang-karier.update', $item) }}" class="mt-6 space-y-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="form_context" value="edit">
                    <input type="hidden" name="editing_id" value="{{ $item->id }}">
                    @include('admin.bidang-karier.partials.form', ['careerField' => $item, 'interestCategories' => $interestCategories, 'submit' => 'Update bidang karier'])
                </form>
            </div>
        </div>
    @endforeach
</div>
@endsection
