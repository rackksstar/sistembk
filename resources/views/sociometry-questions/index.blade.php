@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    createOpen: {{ $errors->any() ? 'true' : 'false' }},
    editOpen: {{ $errors->any() && old('editing_id') ? (int) old('editing_id') : 'null' }}
}">
    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-xs">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <x-section-title title="Soal Sosiometri" description="Kelola pertanyaan kuisioner sosiometri (dipilih & ditolak) beserta aspeknya." />
            <button type="button" x-on:click="createOpen = true" class="w-fit rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-500">Tambah soal</button>
        </div>

        <x-alert class="mt-5" type="success" :message="session('success')" />
        <x-alert class="mt-5" type="error" :message="session('error')" />

        <form method="GET" action="{{ route($routePrefix.'.index') }}" class="mt-6 grid gap-3 lg:grid-cols-[180px_240px_auto_auto]">
            <select name="jenis" class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm focus:border-blue-400 focus:outline-hidden focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/50">
                <option value="">Semua jenis</option>
                @foreach($jenisOptions as $value => $label)
                    <option value="{{ $value }}" @selected($jenis === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="aspek" class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm focus:border-blue-400 focus:outline-hidden focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/50">
                <option value="">Semua aspek</option>
                @foreach($aspekOptions as $value => $label)
                    <option value="{{ $value }}" @selected($aspek === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button class="rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-500 transition">Terapkan</button>
            <x-filter-reset />
        </form>

        <div class="mt-6 overflow-hidden rounded-3xl border border-slate-200 dark:border-slate-700">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">
                        <tr>
                            <th class="px-5 py-4">No</th>
                            <th class="px-5 py-4">Jenis</th>
                            <th class="px-5 py-4">Aspek</th>
                            <th class="px-5 py-4">Pertanyaan</th>
                            <th class="px-5 py-4">Aktif</th>
                            <th class="px-5 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
                        @forelse($questions as $q)
                            <tr>
                                <td class="px-5 py-4 text-slate-700 dark:text-slate-300">{{ $q->urutan }}</td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $q->jenis === \App\Models\SociometryQuestion::JENIS_POSITIF ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300' : 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300' }}">
                                        {{ $q->jenisLabel() }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-slate-600 dark:text-slate-400">{{ $q->aspekLabel() }}</td>
                                <td class="px-5 py-4 font-semibold text-slate-900 dark:text-slate-100">{{ $q->pertanyaan }}</td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $q->is_active ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">
                                        {{ $q->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <button type="button" x-on:click="editOpen = {{ $q->id }}" class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 transition hover:bg-slate-50 dark:hover:bg-slate-800/60">Edit</button>
                                        <form method="POST" action="{{ route($routePrefix.'.destroy', $q) }}" onsubmit="return confirm('Hapus soal ini?')">
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
                                    <x-empty-state title="Belum ada soal sosiometri" description="Tambahkan soal kuisioner sosiometri." />
                                    <div class="mt-4 text-center">
                                        <button type="button" x-on:click="createOpen = true" class="rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-500">Tambah soal</button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
        <div x-on:click.outside="createOpen = false" class="w-full max-w-xl max-h-[calc(100vh-2rem)] overflow-y-auto rounded-3xl bg-white dark:bg-slate-900 p-6 shadow-2xl">
            <x-section-title title="Tambah Soal" description="Isi jenis, aspek, dan teks pertanyaan." />
            <form method="POST" action="{{ route($routePrefix.'.store') }}" class="mt-6 space-y-4">
                @csrf
                @include('sociometry-questions.partials.form', ['question' => null, 'jenisOptions' => $jenisOptions, 'aspekOptions' => $aspekOptions, 'submit' => 'Simpan soal'])
            </form>
        </div>
    </div>

    @foreach($questions as $q)
        <div x-show="editOpen === {{ $q->id }}" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
            <div x-on:click.outside="editOpen = null" class="w-full max-w-xl max-h-[calc(100vh-2rem)] overflow-y-auto rounded-3xl bg-white dark:bg-slate-900 p-6 shadow-2xl">
                <x-section-title title="Edit Soal" description="Perbarui soal sosiometri." />
                <form method="POST" action="{{ route($routePrefix.'.update', $q) }}" class="mt-6 space-y-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="editing_id" value="{{ $q->id }}">
                    @include('sociometry-questions.partials.form', ['question' => $q, 'jenisOptions' => $jenisOptions, 'aspekOptions' => $aspekOptions, 'submit' => 'Update soal'])
                </form>
            </div>
        </div>
    @endforeach
</div>
@endsection
