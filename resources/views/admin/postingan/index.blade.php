@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    createOpen: {{ $errors->any() && old('form_context') !== 'edit' ? 'true' : 'false' }},
    editOpen: {{ $errors->any() && old('form_context') === 'edit' ? (int) old('editing_id') : 'null' }}
}">
    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <x-section-title title="Postingan Artikel" description="Kelola artikel BK untuk dibaca siswa." />
            <button type="button" x-on:click="createOpen = true" class="rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-500">Tambah postingan</button>
        </div>

        <x-alert class="mt-5" type="success" :message="session('success')" />

        <form method="GET" class="mt-6 grid gap-3 md:grid-cols-[1fr_180px_160px_auto]">
            <input name="search" value="{{ $search }}" placeholder="Cari judul atau isi..." class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-sm">
            <select name="kategori" class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-sm">
                <option value="">Semua kategori</option>
                @foreach($kategoris as $kat)
                    <option value="{{ $kat->id }}" @selected((int) $kategoriId === $kat->id)>{{ $kat->name }}</option>
                @endforeach
            </select>
            <select name="status" class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-sm">
                <option value="">Semua status</option>
                @foreach(\App\Models\Postingan::STATUSES as $value => $label)
                    <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button class="rounded-2xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-500">Filter</button>
        </form>
    </section>

    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
        <div class="overflow-x-auto rounded-3xl border border-slate-200 dark:border-slate-700">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">
                    <tr>
                        <th class="px-5 py-4">Judul</th>
                        <th class="px-5 py-4">Kategori</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
                    @forelse($postingan as $item)
                        <tr>
                            <td class="px-5 py-4 font-semibold text-slate-900 dark:text-slate-100">{{ $item->judul }}</td>
                            <td class="px-5 py-4 text-slate-600 dark:text-slate-400">{{ $item->kategori?->name ?? '-' }}</td>
                            <td class="px-5 py-4">{{ $item->statusLabel() }}</td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <button type="button" x-on:click="editOpen = {{ $item->id }}" class="rounded-2xl border border-slate-200 dark:border-slate-700 px-3 py-1.5 text-xs font-semibold">Edit</button>
                                    <form method="POST" action="{{ route('admin.postingan.destroy', $item) }}" onsubmit="return confirm('Hapus postingan ini?')">
                                        @csrf @method('DELETE')
                                        <button class="rounded-2xl bg-red-600 px-3 py-1.5 text-xs font-semibold text-white">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-8">
                            <x-empty-state title="Belum ada postingan" description="Buat artikel pertama untuk siswa." />
                            <div class="mt-4 text-center">
                                <button type="button" x-on:click="createOpen = true" class="rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-500">Tambah postingan</button>
                            </div>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-5">{{ $postingan->links() }}</div>
    </section>

    <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
        <div x-on:click.outside="createOpen = false" class="max-h-[90vh] w-full max-w-xl overflow-y-auto rounded-3xl bg-white dark:bg-slate-900 p-6 shadow-2xl">
            <x-section-title title="Tambah Postingan" description="Artikel baru untuk siswa." />
            <form method="POST" action="{{ route('admin.postingan.store') }}" enctype="multipart/form-data" class="mt-6 space-y-4">
                @csrf
                <input type="hidden" name="form_context" value="create">
                @include('admin.postingan.partials.form', ['item' => null, 'submit' => 'Simpan postingan'])
            </form>
        </div>
    </div>

    @foreach($postingan as $item)
        <div x-show="editOpen === {{ $item->id }}" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
            <div x-on:click.outside="editOpen = null" class="max-h-[90vh] w-full max-w-xl overflow-y-auto rounded-3xl bg-white dark:bg-slate-900 p-6 shadow-2xl">
                <x-section-title title="Edit Postingan" description="{{ $item->judul }}" />
                <form method="POST" action="{{ route('admin.postingan.update', $item) }}" enctype="multipart/form-data" class="mt-6 space-y-4">
                    @csrf @method('PUT')
                    <input type="hidden" name="form_context" value="edit">
                    <input type="hidden" name="editing_id" value="{{ $item->id }}">
                    @include('admin.postingan.partials.form', ['item' => $item, 'submit' => 'Update postingan'])
                </form>
            </div>
        </div>
    @endforeach
</div>
@endsection
