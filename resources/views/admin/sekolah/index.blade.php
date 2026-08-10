@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    createOpen: {{ $errors->any() && old('form_context') !== 'edit' ? 'true' : 'false' }},
    editOpen: {{ $errors->any() && old('form_context') === 'edit' ? (int) old('editing_id') : 'null' }}
}">
    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <x-section-title title="Daftar Sekolah MOU" description="Kelola sekolah yang sudah MOU dengan PCR dan bisa dipilih saat Guru BK mendaftar." />
            <button type="button" x-on:click="createOpen = !createOpen" class="w-fit rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-500">
                <span x-text="createOpen ? 'Tutup formulir' : 'Tambah sekolah MOU'"></span>
            </button>
        </div>

        <x-alert class="mt-5" type="success" :message="session('success')" />
        <x-alert class="mt-5" type="error" :message="session('error')" />

        <form method="POST" action="{{ route('admin.sekolah.store') }}" enctype="multipart/form-data" x-show="createOpen" x-cloak class="mt-6 rounded-3xl border border-blue-100 dark:border-blue-900/50 bg-blue-50 dark:bg-blue-950/30 p-5">
            @csrf
                <input type="hidden" name="form_context" value="create">
            <input type="hidden" name="is_mou" value="1">
            <input type="hidden" name="is_active" value="1">

            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <x-section-title title="Input Sekolah MOU" description="Tambahkan sekolah MOU baru ke master data." />
                <button type="submit" class="rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-500">Simpan sekolah MOU</button>
            </div>

            <div class="mt-5 grid gap-4 lg:grid-cols-2">
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100" for="create-nama">Nama Sekolah</label>
                    <input id="create-nama" name="nama" value="{{ old('nama') }}" required class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/50" placeholder="SMA Negeri 1 ..." />
                    <x-input-error :messages="$errors->get('nama')" class="text-sm text-red-600" />
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100" for="create-npsn">NPSN</label>
                    <input id="create-npsn" name="npsn" value="{{ old('npsn') }}" required class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/50" placeholder="NPSN sekolah" />
                    <x-input-error :messages="$errors->get('npsn')" class="text-sm text-red-600" />
                </div>

                <div class="space-y-2 lg:col-span-2">
                    <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100" for="create-alamat">Alamat Sekolah</label>
                    <textarea id="create-alamat" name="alamat" required rows="3" class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/50" placeholder="Alamat lengkap sekolah">{{ old('alamat') }}</textarea>
                    <x-input-error :messages="$errors->get('alamat')" class="text-sm text-red-600" />
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100" for="create-logo">Logo (opsional)</label>
                    <input id="create-logo" name="logo" type="file" accept="image/*" class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/50" />
                    <x-input-error :messages="$errors->get('logo')" class="text-sm text-red-600" />
                </div>
            </div>
        </form>

        <form method="GET" action="{{ route('admin.sekolah.index') }}" class="mt-6 grid gap-3 lg:grid-cols-[minmax(0,1fr)_160px_160px_auto]">
            <input name="search" value="{{ $search }}" class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/50" placeholder="Cari nama/NPSN..." />
            <select name="mou" class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/50">
                <option value="">Semua status MOU</option>
                <option value="1" @selected($mou === '1')>Sudah MOU</option>
                <option value="0" @selected($mou === '0')>Belum MOU</option>
            </select>
            <select name="active" class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/50">
                <option value="">Semua status</option>
                <option value="1" @selected($active === '1')>Aktif</option>
                <option value="0" @selected($active === '0')>Nonaktif</option>
            </select>
            <button class="rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-500 transition">Terapkan</button>
        </form>

        <div class="mt-6 overflow-hidden rounded-3xl border border-slate-200 dark:border-slate-700">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">
                        <tr>
                            <th class="px-5 py-4">Nama</th>
                            <th class="px-5 py-4">NPSN</th>
                            <th class="px-5 py-4">MOU</th>
                            <th class="px-5 py-4">Aktivasi</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
                        @forelse($sekolahs as $sekolah)
                            <tr>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        @if($sekolah->logo_path)
                                            <img src="{{ asset('storage/'.$sekolah->logo_path) }}" alt="" class="h-10 w-10 rounded-xl object-cover">
                                        @endif
                                        <div>
                                            <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $sekolah->nama }}</p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $sekolah->alamat ?? '-' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-slate-600 dark:text-slate-400">{{ $sekolah->npsn ?? '-' }}</td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $sekolah->is_mou ? 'bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300' : 'bg-amber-50 dark:bg-amber-950/40 text-amber-700' }}">
                                        {{ $sekolah->is_mou ? 'Sudah MOU' : 'Belum MOU' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-slate-600 dark:text-slate-400">{{ $sekolah->tanggal_aktivasi?->format('d M Y') ?? '-' }}</td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $sekolah->is_active ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">
                                        {{ $sekolah->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <button type="button" x-on:click="editOpen = {{ $sekolah->id }}" class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 transition hover:bg-slate-50 dark:hover:bg-slate-800/60">Edit</button>
                                        <form method="POST" action="{{ route('admin.sekolah.destroy', $sekolah) }}" onsubmit="return confirm('Hapus sekolah ini?')">
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
                                    <x-empty-state title="Belum ada sekolah" description="Tambahkan sekolah untuk mulai mengelola kelas dan guru BK." />
                                    <div class="mt-4 text-center">
                                        <button type="button" x-on:click="createOpen = true" class="rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-500">Tambah sekolah MOU</button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-5">
            {{ $sekolahs->links() }}
        </div>
    </section>

    @foreach($sekolahs as $sekolah)
        <div x-show="editOpen === {{ $sekolah->id }}" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
            <div x-on:click.outside="editOpen = null" class="w-full max-w-xl rounded-3xl bg-white dark:bg-slate-900 p-6 shadow-2xl">
                <div class="flex items-start justify-between gap-4">
                    <x-section-title title="Edit Sekolah" description="Perbarui data sekolah." />
                    <button type="button" x-on:click="editOpen = null" class="rounded-full bg-slate-100 dark:bg-slate-800 px-3 py-1 text-sm font-semibold text-slate-600 dark:text-slate-400">x</button>
                </div>

                <form method="POST" action="{{ route('admin.sekolah.update', $sekolah) }}" enctype="multipart/form-data" class="mt-6 space-y-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="form_context" value="edit">
                    <input type="hidden" name="editing_id" value="{{ $sekolah->id }}">
                    @include('admin.sekolah.partials.form', ['sekolah' => $sekolah, 'submit' => 'Update sekolah'])
                </form>
            </div>
        </div>
    @endforeach
</div>
@endsection
