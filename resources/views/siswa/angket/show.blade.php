@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ confirmOpen: false }">
    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <x-section-title
                title="Isi Angket BK"
                description="Jawab semua pertanyaan di bawah ini, lalu simpan sekaligus."
            />
            <a href="{{ route('siswa.angket.index') }}" class="w-fit rounded-2xl border border-slate-200 dark:border-slate-700 px-5 py-3 text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60">
                Kembali
            </a>
        </div>

        @if($pertanyaan->isEmpty())
            <div class="mt-6">
                <x-empty-state title="Belum ada soal angket aktif" description="Silakan kembali nanti setelah pertanyaan tersedia." />
            </div>
        @else
            <form
                id="angket-form"
                action="{{ route('siswa.angket.store') }}"
                method="POST"
                class="mt-6 space-y-5"
                x-on:submit.prevent="confirmOpen = true"
            >
                @csrf

                @foreach($pertanyaan as $index => $soal)
                    @php($jawabanLama = $jawabanBySoal[$soal->id] ?? null)
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50/60 p-5">
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Soal {{ $index + 1 }}</p>
                        <p class="mt-2 text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $soal->teks_pertanyaan }}</p>
                        <div class="mt-4 space-y-2">
                            <x-input-label :for="'jawaban_'.$soal->id" value="Jawaban Anda" />
                            <textarea
                                id="jawaban_{{ $soal->id }}"
                                name="jawaban[{{ $soal->id }}]"
                                rows="3"
                                required
                                maxlength="500"
                                class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-3 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/50"
                                placeholder="Tulis jawaban Anda..."
                            >{{ old('jawaban.'.$soal->id, $jawabanLama) }}</textarea>
                            <x-input-error :messages="$errors->get('jawaban.'.$soal->id)" />
                        </div>
                    </div>
                @endforeach

                <div class="flex justify-end">
                    <x-primary-button type="submit">Simpan Jawaban</x-primary-button>
                </div>
            </form>
        @endif
    </section>

    <div
        x-cloak
        x-show="confirmOpen"
        x-transition.opacity
        class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/60 px-4 backdrop-blur-sm"
        role="dialog"
        aria-modal="true"
    >
        <div x-on:click.outside="confirmOpen = false" class="w-full max-w-md rounded-2xl border border-white dark:border-slate-700 bg-white dark:bg-slate-900 p-6 text-center shadow-2xl shadow-slate-950/20">
            <p class="text-xs font-bold uppercase tracking-[0.24em] text-blue-600">Konfirmasi</p>
            <h2 class="mt-3 text-xl font-bold text-slate-950 dark:text-white">Yakin ingin menyimpan jawaban?</h2>
            <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-400">Jawaban yang sudah ada akan diperbarui jika Anda mengubahnya.</p>
            <div class="mt-6 flex flex-col items-center justify-center gap-3 sm:flex-row">
                <button type="button" x-on:click="confirmOpen = false" class="inline-flex items-center justify-center rounded-full border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-5 py-3 text-sm font-semibold text-slate-700 dark:text-slate-300 transition hover:border-blue-200 dark:border-blue-800 hover:text-blue-700 dark:text-blue-300">
                    Batal
                </button>
                <button
                    type="button"
                    x-on:click="document.getElementById('angket-form').submit()"
                    class="inline-flex items-center justify-center rounded-full bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-500"
                >
                    Ya, simpan
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
