@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ createOpen: {{ $errors->any() ? 'true' : 'false' }}, editOpen: null }">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <x-section-title
                title="Manajemen Pengguna"
                description="Kelola akun admin, Guru BK, dan siswa dari satu tabel."
            />
            <button type="button" x-on:click="createOpen = true" class="w-fit rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-500">Tambah pengguna</button>
        </div>

        <x-alert class="mt-5" type="success" :message="session('success')" />
        <x-alert class="mt-5" type="error" :message="session('error')" />

        <form method="GET" action="{{ route('admin.users.index') }}" class="mt-6 grid gap-3 lg:grid-cols-[minmax(0,1fr)_180px_180px_auto]">
            <input name="search" value="{{ $search }}" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="Cari nama atau email..." />
            <select name="role" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
                <option value="">Semua role</option>
                @foreach($roles as $item)
                    <option value="{{ $item }}" @selected($role === $item)>{{ ucfirst($item) }}</option>
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
                            <th class="px-5 py-4">Role</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($users as $managedUser)
                            <tr>
                                <td class="px-5 py-4 font-semibold text-slate-900">{{ $managedUser->name }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $managedUser->email }}</td>
                                <td class="px-5 py-4 capitalize text-slate-700">{{ $managedUser->role }}</td>
                                <td class="px-5 py-4"><x-status-badge :status="$managedUser->status" /></td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <button type="button" x-on:click="editOpen = {{ $managedUser->id }}" class="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">Edit</button>
                                        <form method="POST" action="{{ route('admin.users.destroy', $managedUser) }}" onsubmit="return confirm('Hapus akun ini?')">
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
                                    <x-empty-state title="Pengguna tidak ditemukan" description="Coba ubah kata kunci pencarian atau filter role." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-5">
            {{ $users->links() }}
        </div>
    </section>

    <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
        <div x-on:click.outside="createOpen = false" class="w-full max-w-xl rounded-3xl bg-white p-6 shadow-2xl">
            <div class="flex items-start justify-between gap-4">
                <x-section-title title="Tambah Pengguna" description="Buat akun baru untuk admin, Guru BK, atau siswa." />
                <button type="button" x-on:click="createOpen = false" class="rounded-full bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-600">x</button>
            </div>

            <form method="POST" action="{{ route('admin.users.store') }}" class="mt-6 space-y-4">
                @csrf
                @include('admin.users.partials.form', ['roles' => $roles, 'statuses' => $statuses, 'user' => null, 'submit' => 'Simpan pengguna'])
            </form>
        </div>
    </div>

    @foreach($users as $managedUser)
        <div x-show="editOpen === {{ $managedUser->id }}" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
            <div x-on:click.outside="editOpen = null" class="w-full max-w-xl rounded-3xl bg-white p-6 shadow-2xl">
                <div class="flex items-start justify-between gap-4">
                    <x-section-title title="Edit Pengguna" description="Perbarui data akun pengguna." />
                    <button type="button" x-on:click="editOpen = null" class="rounded-full bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-600">x</button>
                </div>

                <form method="POST" action="{{ route('admin.users.update', $managedUser) }}" class="mt-6 space-y-4">
                    @csrf
                    @method('PUT')
                    @include('admin.users.partials.form', ['roles' => $roles, 'statuses' => $statuses, 'user' => $managedUser, 'submit' => 'Update pengguna'])
                </form>
            </div>
        </div>
    @endforeach
</div>
@endsection
