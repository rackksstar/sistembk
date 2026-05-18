@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ createOpen: {{ $errors->any() ? 'true' : 'false' }}, editOpen: null }">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <x-section-title title="Manajemen Kelas" description="Kelola kelas per sekolah dengan filter jenjang." />
            <button type="button" x-on:click="createOpen = true" class="w-fit rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-500">Tambah kelas</button>
        </div>

        <x-alert class="mt-5" type="success" :message="session('success')" />
        <x-alert class="mt-5" type="error" :message="session('error')" />

        <form method="GET" action="{{ route('admin.kelas.index') }}" class="mt-6 grid gap-3 lg:grid-cols-[minmax(0,1fr)_260px_180px_auto]">
            <input name="search" value="{{ $search }}" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="Cari nama kelas..." />
            <select name="sekolah_id" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
                <option value="">Semua sekolah</option>
                @foreach($sekolahs as $s)
                    <option value="{{ $s->id }}" @selected((string) $sekolahId === (string) $s->id)>{{ $s->nama }}</option>
                @endforeach
            </select>
            <select name="jenjang" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
                <option value="">Semua jenjang</option>
                @foreach($jenjangOptions as $item)
                    <option value="{{ $item }}" @selected($jenjang === $item)>{{ $item }}</option>
                @endforeach
            </select>
            <button class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-700">Terapkan</button>
        </form>

        <div class="mt-6 overflow-hidden rounded-3xl border border-slate-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                        <tr>
                            <th class="px-5 py-4">Kelas</th>
                            <th class="px-5 py-4">Sekolah</th>
                            <th class="px-5 py-4">Jenjang</th>
                            <th class="px-5 py-4">Tingkatan</th>
                            <th class="px-5 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($kelas as $item)
                            <tr>
                                <td class="px-5 py-4 font-semibold text-slate-900">{{ $item->nama }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $item->sekolah?->nama ?? '-' }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $item->jenjang ?? '-' }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $item->tingkatan ?? '-' }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <button type="button" x-on:click="editOpen = {{ $item->id }}" class="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">Edit</button>
                                        <form method="POST" action="{{ route('admin.kelas.destroy', $item) }}" onsubmit="return confirm('Hapus kelas ini?')">
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
                                    <x-empty-state title="Belum ada kelas" description="Tambahkan kelas untuk mengelompokkan siswa berdasarkan sekolah." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-5">{{ $kelas->links() }}</div>
    </section>

    <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
        <div x-on:click.outside="createOpen = false" class="w-full max-w-xl rounded-3xl bg-white p-6 shadow-2xl">
            <x-section-title title="Tambah Kelas" description="Pilih sekolah lalu isi identitas kelas." />
            <form method="POST" action="{{ route('admin.kelas.store') }}" class="mt-6 space-y-4">
                @csrf
                @include('admin.kelas.partials.form', ['kelas' => null, 'sekolahs' => $sekolahs, 'submit' => 'Simpan kelas'])
            </form>
        </div>
    </div>

    @foreach($kelas as $item)
        <div x-show="editOpen === {{ $item->id }}" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
            <div x-on:click.outside="editOpen = null" class="w-full max-w-xl rounded-3xl bg-white p-6 shadow-2xl">
                <x-section-title title="Edit Kelas" description="Perbarui identitas kelas." />
                <form method="POST" action="{{ route('admin.kelas.update', $item) }}" class="mt-6 space-y-4">
                    @csrf
                    @method('PUT')
                    @include('admin.kelas.partials.form', ['kelas' => $item, 'sekolahs' => $sekolahs, 'submit' => 'Update kelas'])
                </form>
            </div>
        </div>
    @endforeach
</div>
@endsection

