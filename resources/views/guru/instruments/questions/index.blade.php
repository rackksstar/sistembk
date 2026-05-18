@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ createOpen: {{ $errors->any() ? 'true' : 'false' }}, editOpen: null }">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <x-section-title title="Soal Instrumen Asesmen" description="Kelola soal Minat Bakat, Gaya Belajar, dan Masalah." />
            <button type="button" x-on:click="createOpen = true" class="w-fit rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-500">Tambah soal</button>
        </div>

        <x-alert class="mt-5" type="success" :message="session('success')" />
        @if($errors->any())
            <x-alert class="mt-5" type="error" message="Periksa kembali data soal dan pilihan jawaban." />
        @endif

        <form method="GET" action="{{ route('guru.instrument-questions.index') }}" class="mt-6 grid gap-3 sm:grid-cols-[minmax(0,1fr)_auto]">
            <x-form-select name="category">
                <option value="">Semua kategori</option>
                @foreach($categories as $value => $label)
                    <option value="{{ $value }}" @selected($category === $value)>{{ $label }}</option>
                @endforeach
            </x-form-select>
            <button class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-700">Filter</button>
        </form>
    </section>

    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-[0.16em] text-slate-500">
                    <tr>
                        <th class="px-5 py-4">Kategori</th>
                        <th class="px-5 py-4">Soal</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($questions as $question)
                        <tr>
                            <td class="px-5 py-4 font-semibold text-slate-900">{{ $question->categoryLabel() }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $question->question }}</td>
                            <td class="px-5 py-4">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $question->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $question->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex gap-2">
                                    <button type="button" x-on:click="editOpen = {{ $question->id }}" class="rounded-xl border border-slate-200 px-3 py-2 font-semibold text-slate-700 hover:bg-slate-50">Edit</button>
                                    <form method="POST" action="{{ route('guru.instrument-questions.destroy', $question) }}" onsubmit="return confirm('Hapus soal ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="rounded-xl bg-red-600 px-3 py-2 font-semibold text-white hover:bg-red-500">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-8"><x-empty-state title="Belum ada soal" description="Tambahkan soal instrumen pertama untuk mulai mengumpulkan jawaban siswa." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{ $questions->links() }}

    <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
        <div x-on:click.outside="createOpen = false" class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-3xl bg-white p-6 shadow-2xl">
            <div class="flex items-start justify-between gap-4">
                <x-section-title title="Tambah Soal" description="Buat soal baru beserta bobot skor." />
                <button type="button" x-on:click="createOpen = false" class="rounded-full bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-600">x</button>
            </div>
            <form method="POST" action="{{ route('guru.instrument-questions.store') }}" class="mt-6">
                @csrf
                @include('guru.instruments.questions.partials.form', ['question' => null, 'submit' => 'Simpan soal'])
            </form>
        </div>
    </div>

    @foreach($questions as $question)
        <div x-show="editOpen === {{ $question->id }}" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
            <div x-on:click.outside="editOpen = null" class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-3xl bg-white p-6 shadow-2xl">
                <div class="flex items-start justify-between gap-4">
                    <x-section-title title="Edit Soal" description="Perbarui soal dan skor jawaban." />
                    <button type="button" x-on:click="editOpen = null" class="rounded-full bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-600">x</button>
                </div>
                <form method="POST" action="{{ route('guru.instrument-questions.update', $question) }}" class="mt-6">
                    @csrf
                    @method('PUT')
                    @include('guru.instruments.questions.partials.form', ['question' => $question, 'submit' => 'Update soal'])
                </form>
            </div>
        </div>
    @endforeach
</div>
@endsection
