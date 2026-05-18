@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ createOpen: {{ $errors->any() ? 'true' : 'false' }}, editOpen: null }">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <x-section-title title="Manajemen Guru BK" description="CRUD Guru BK sekaligus akun user (role=guru)." />
            <button type="button" x-on:click="createOpen = true" class="w-fit rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-500">Tambah Guru BK</button>
        </div>

        <x-alert class="mt-5" type="success" :message="session('success')" />
        <x-alert class="mt-5" type="error" :message="session('error')" />

        <form method="GET" action="{{ route('admin.guru-bk.index') }}" class="mt-6 grid gap-3 lg:grid-cols-[minmax(0,1fr)_260px_180px_auto]">
            <input name="search" value="{{ $search }}" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="Cari nama/email/NIP..." />
            <select name="sekolah_id" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
                <option value="">Semua sekolah</option>
                @foreach($sekolahs as $s)
                    <option value="{{ $s->id }}" @selected((string) $sekolahId === (string) $s->id)>{{ $s->nama }}</option>
                @endforeach
            </select>
            <select name="status" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
                <option value="">Semua status</option>
                @foreach($statuses as $item)
                    <option value="{{ $item }}" @selected($status === $item)>{{ ucfirst($item) }}</option>
                @endforeach
            </select>
            <button class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-700">Terapkan</button>
        </form>

        <div class="mt-6 overflow-hidden rounded-3xl border border-slate-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                        <tr>
                            <th class="px-5 py-4">Nama</th>
                            <th class="px-5 py-4">Email</th>
                            <th class="px-5 py-4">NIP</th>
                            <th class="px-5 py-4">Sekolah</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($guruBks as $item)
                            <tr>
                                <td class="px-5 py-4 font-semibold text-slate-900">{{ $item->user?->name }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $item->user?->email }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $item->nip ?? '-' }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $item->sekolah?->nama ?? '-' }}</td>
                                <td class="px-5 py-4"><x-status-badge :status="$item->user?->status" /></td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <button type="button" x-on:click="editOpen = {{ $item->id }}" class="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">Edit</button>
                                        <form method="POST" action="{{ route('admin.guru-bk.destroy', $item) }}" onsubmit="return confirm('Hapus Guru BK ini? Akun user juga akan terhapus.')">
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
                                    <x-empty-state title="Belum ada data Guru BK" description="Tambahkan Guru BK untuk mulai mengelola sesi konseling." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-5">{{ $guruBks->links() }}</div>
    </section>

    <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
        <div x-on:click.outside="createOpen = false" class="w-full max-w-xl rounded-3xl bg-white p-6 shadow-2xl">
            <x-section-title title="Tambah Guru BK" description="Buat akun dan profil Guru BK sekaligus." />
            <form method="POST" action="{{ route('admin.guru-bk.store') }}" class="mt-6 space-y-4">
                @csrf
                @include('admin.guru-bk.partials.form', ['guruBk' => null, 'sekolahs' => $sekolahs, 'statuses' => $statuses, 'submit' => 'Simpan Guru BK'])
            </form>
        </div>
    </div>

    @foreach($guruBks as $item)
        <div x-show="editOpen === {{ $item->id }}" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
            <div x-on:click.outside="editOpen = null" class="w-full max-w-xl rounded-3xl bg-white p-6 shadow-2xl">
                <x-section-title title="Edit Guru BK" description="Perbarui data akun dan profil." />
                <form method="POST" action="{{ route('admin.guru-bk.update', $item) }}" class="mt-6 space-y-4">
                    @csrf
                    @method('PUT')
                    @include('admin.guru-bk.partials.form', ['guruBk' => $item, 'sekolahs' => $sekolahs, 'statuses' => $statuses, 'submit' => 'Update Guru BK'])
                </form>
            </div>
        </div>
    @endforeach
</div>
@endsection

