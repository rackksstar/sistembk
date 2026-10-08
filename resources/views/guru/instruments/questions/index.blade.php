@extends('layouts.app')

@section('content')
<div
    class="space-y-6"
    x-data="modalCrud({{ $errors->any() ? 'true' : 'false' }}, null)"
>
    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <x-section-title
                    :title="$module === 'key' ? 'Soal Minat Bakat Kuliah (Key)' : 'Soal Instrumen Siap Kerja (Yola)'"
                    :description="$module === 'key'
                        ? 'Kelola soal RIASEC / Talents Mapping untuk lanjut kuliah (rekomendasi PCR).'
                        : 'Kelola soal Minat Bakat Kerja, Strategi Belajar, Kepribadian, dan Masalah.'"
                />
                <span class="mt-2 inline-flex rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-[0.14em] {{ $module === 'key' ? 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300' : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' }}">
                    {{ $module === 'key' ? 'Key · Kuliah' : 'Yola · Kerja' }}
                </span>
            </div>
            <button type="button" x-on:click="openCreate()" class="w-fit rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-500">Tambah soal</button>
        </div>

        <x-alert class="mt-5" type="success" :message="session('success')" />
        @if($errors->any())
            <x-alert class="mt-5" type="error" message="Periksa kembali data soal dan pilihan jawaban." />
        @endif

        <form method="GET" action="{{ route('guru.instrument-questions.index') }}" class="mt-6 grid gap-3 {{ $module === 'key' ? 'lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_minmax(0,1fr)_auto]' : 'lg:grid-cols-[minmax(0,1fr)_auto]' }}">
            <input type="hidden" name="module" value="{{ $module }}">
            <x-form-select name="category" data-placeholder="Semua kategori instrumen">
                <option value="">Semua kategori instrumen</option>
                @foreach($categories as $value => $label)
                    <option value="{{ $value }}" @selected($category === $value)>{{ $label }}</option>
                @endforeach
            </x-form-select>
            @if($module === 'key')
                <x-form-select name="interest_category_id" data-placeholder="Semua kategori minat">
                    <option value="">Semua kategori minat</option>
                    @foreach($interestCategories as $interestCategory)
                        <option value="{{ $interestCategory->id }}" @selected((string) $interest_category_id === (string) $interestCategory->id)>
                            {{ $interestCategory->kode }} — {{ $interestCategory->nama }}
                        </option>
                    @endforeach
                </x-form-select>
                <x-form-select name="jenjang_target" data-placeholder="Semua jenjang">
                    <option value="">Semua jenjang</option>
                    @foreach($jenjangTargets as $target)
                        <option value="{{ $target }}" @selected($jenjang_target === $target)>
                            {{ $target === 'semua' ? 'Semua' : $target }}
                        </option>
                    @endforeach
                </x-form-select>
            @endif
            <button class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-700">Filter</button>
        </form>
    </section>

    <section class="overflow-hidden rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-800 text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-left text-xs font-bold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">
                    <tr>
                        <th class="px-5 py-4">Kategori Instrumen</th>
                        <th class="px-5 py-4">Soal</th>
                        <th class="px-5 py-4">Kategori Minat</th>
                        <th class="px-5 py-4">Jenjang</th>
                        <th class="px-5 py-4">Bobot</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($questions as $question)
                        <tr>
                            <td class="px-5 py-4 font-semibold text-slate-900 dark:text-slate-100">{{ $question->categoryLabel() }}</td>
                            <td class="px-5 py-4 text-slate-600 dark:text-slate-400">{{ $question->question }}</td>
                            <td class="px-5 py-4 text-slate-600 dark:text-slate-400">
                                @if($question->category === \App\Models\InstrumentQuestion::CATEGORY_MINAT_BAKAT)
                                    @if($question->interestCategory)
                                        <span class="font-medium text-slate-800 dark:text-slate-200">
                                            {{ $question->interestCategory->kode }} — {{ $question->interestCategory->nama }}
                                        </span>
                                    @else
                                        <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-950/40 dark:text-amber-300">
                                            Belum dikategorikan
                                        </span>
                                    @endif
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-slate-600 dark:text-slate-400">
                                {{ $question->jenjang_target === 'semua' ? 'Semua' : $question->jenjang_target }}
                            </td>
                            <td class="px-5 py-4 text-slate-600 dark:text-slate-400">{{ $question->bobot }}</td>
                            <td class="px-5 py-4">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $question->is_active ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">
                                    {{ $question->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex gap-2">
                                    <button type="button" x-on:click="openEdit({{ $question->id }})" class="rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60">Edit</button>
                                    <form method="POST" action="{{ route('guru.instrument-questions.destroy', $question) }}" onsubmit="return confirm('Hapus soal ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="rounded-xl bg-red-600 px-3 py-2 font-semibold text-white hover:bg-red-500">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-8"><x-empty-state title="Belum ada soal" description="Tambahkan soal instrumen pertama untuk mulai mengumpulkan jawaban siswa." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{ $questions->links() }}

    <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
        <div x-on:click.outside="createOpen = false" class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-3xl bg-white dark:bg-slate-900 p-6 shadow-2xl">
            <div class="flex items-start justify-between gap-4">
                <x-section-title title="Tambah Soal" description="Buat soal baru beserta bobot skor." />
                <button type="button" x-on:click="createOpen = false" class="rounded-full bg-slate-100 dark:bg-slate-800 px-3 py-1 text-sm font-semibold text-slate-600 dark:text-slate-400">x</button>
            </div>
            <form method="POST" action="{{ route('guru.instrument-questions.store') }}" class="mt-6">
                @csrf
                @include('guru.instruments.questions.partials.form', ['question' => null, 'submit' => 'Simpan soal'])
            </form>
        </div>
    </div>

    @foreach($questions as $question)
        <div x-show="editOpen === {{ $question->id }}" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
            <div x-on:click.outside="editOpen = null" class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-3xl bg-white dark:bg-slate-900 p-6 shadow-2xl">
                <div class="flex items-start justify-between gap-4">
                    <x-section-title title="Edit Soal" description="Perbarui soal dan skor jawaban." />
                    <button type="button" x-on:click="editOpen = null" class="rounded-full bg-slate-100 dark:bg-slate-800 px-3 py-1 text-sm font-semibold text-slate-600 dark:text-slate-400">x</button>
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
