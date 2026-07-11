@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    createOpen: {{ $errors->any() && old('form_context') !== 'edit' ? 'true' : 'false' }},
    editOpen: {{ $errors->any() && old('form_context') === 'edit' ? (int) old('editing_id') : 'null' }}
}">
    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <x-section-title title="Kategori Postingan" description="Kelola kategori untuk konten postingan." />
            <button type="button" x-on:click="createOpen = true" class="w-fit rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-500">Tambah kategori</button>
        </div>

        <x-alert class="mt-5" type="success" :message="session('success')" />
        <x-alert class="mt-5" type="error" :message="session('error')" />

        <form method="GET" action="{{ route('admin.kategori-postingan.index') }}" class="mt-6 grid gap-3 md:grid-cols-[1fr_auto]">
            <input name="search" value="{{ $search }}" class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/50" placeholder="Cari kategori..." />
            <button class="rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-500 transition">Cari</button>
        </form>

        <div class="mt-6 overflow-hidden rounded-3xl border border-slate-200 dark:border-slate-700">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">
                        <tr>
                            <th class="px-5 py-4">Nama</th>
                            <th class="px-5 py-4">Slug</th>
                            <th class="px-5 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
                        @forelse($categories as $cat)
                            <tr>
                                <td class="px-5 py-4 font-semibold text-slate-900 dark:text-slate-100">{{ $cat->name }}</td>
                                <td class="px-5 py-4 text-slate-600 dark:text-slate-400">{{ $cat->slug }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <button type="button" x-on:click="editOpen = {{ $cat->id }}" class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 transition hover:bg-slate-50 dark:hover:bg-slate-800/60">Edit</button>
                                        <form method="POST" action="{{ route('admin.kategori-postingan.destroy', $cat) }}" onsubmit="return confirm('Hapus kategori ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="rounded-2xl bg-red-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-red-500">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-5 py-6">
                                    <x-empty-state title="Belum ada kategori" description="Tambahkan kategori pertama untuk postingan." />
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
        <div x-on:click.outside="createOpen = false" class="w-full max-w-xl rounded-3xl bg-white dark:bg-slate-900 p-6 shadow-2xl">
            <x-section-title title="Tambah Kategori" description="Nama kategori akan dibuatkan slug otomatis." />
            <form method="POST" action="{{ route('admin.kategori-postingan.store') }}" class="mt-6 space-y-4">
                @csrf
                <input type="hidden" name="form_context" value="create">
                @include('admin.kategori-postingan.partials.form', ['kategori' => null, 'submit' => 'Simpan kategori'])
            </form>
        </div>
    </div>

    @foreach($categories as $cat)
        <div x-show="editOpen === {{ $cat->id }}" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
            <div x-on:click.outside="editOpen = null" class="w-full max-w-xl rounded-3xl bg-white dark:bg-slate-900 p-6 shadow-2xl">
                <x-section-title title="Edit Kategori" description="Perbarui nama kategori." />
                <form method="POST" action="{{ route('admin.kategori-postingan.update', $cat) }}" class="mt-6 space-y-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="form_context" value="edit">
                    <input type="hidden" name="editing_id" value="{{ $cat->id }}">
                    @include('admin.kategori-postingan.partials.form', ['kategori' => $cat, 'submit' => 'Update kategori'])
                </form>
            </div>
        </div>
    @endforeach
</div>
@endsection

