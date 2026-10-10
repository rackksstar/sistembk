@extends('layouts.app')

@section('content')
@php
    use App\Models\SociometryQuestion;
    $positif = $questions->where('jenis', SociometryQuestion::JENIS_POSITIF);
    $negatif = $questions->where('jenis', SociometryQuestion::JENIS_NEGATIF);
    $sections = [
        ['title' => 'Pertanyaan Positif (Dipilih)', 'accent' => 'emerald', 'items' => $positif],
        ['title' => 'Pertanyaan Negatif (Ditolak)', 'accent' => 'rose', 'items' => $negatif],
    ];
@endphp
<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-xs">
        <x-section-title title="Sosiometri" description="Jawab setiap pertanyaan dengan memilih teman beserta alasannya. Data ini membantu Guru BK melihat pola hubungan siswa." />
        <x-alert class="mt-5" type="success" :message="session('success')" />
        @if($errors->any())
            <x-alert class="mt-5" type="error" message="Periksa kembali jawaban Anda." />
        @endif
        <p class="mt-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 p-4 text-sm text-slate-600 dark:text-slate-400">
            Jawaban penolakan bersifat rahasia dan hanya dapat diakses oleh Guru BK. Jawaban baru akan menggantikan jawaban sebelumnya.
        </p>
    </section>

    @if($questions->isEmpty())
        <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-xs">
            <x-empty-state title="Belum ada pertanyaan sosiometri" description="Guru BK belum menambahkan pertanyaan. Silakan cek kembali nanti." />
        </section>
    @else
        <form method="POST" action="{{ route('siswa.sociometry.store') }}" class="space-y-6">
            @csrf

            @foreach($sections as $section)
                @continue($section['items']->isEmpty())
                <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-xs">
                    <x-section-title :title="$section['title']" :description="'Jumlah pertanyaan: '.$section['items']->count()" />

                    <div class="mt-6 grid gap-5">
                        @foreach($section['items'] as $question)
                            @php $answer = $responses[$question->id] ?? null; @endphp
                            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/40 p-5">
                                <div class="flex items-start gap-3">
                                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-blue-600 text-xs font-bold text-white">{{ $loop->iteration }}</span>
                                    <div class="min-w-0">
                                        <label class="block text-sm font-semibold leading-relaxed text-slate-900 dark:text-slate-100" for="q-{{ $question->id }}">
                                            {{ $question->pertanyaan }}
                                        </label>
                                        <p class="mt-1 text-xs font-semibold uppercase tracking-[0.14em] text-slate-400 dark:text-slate-500">{{ $question->aspekLabel() }}</p>
                                    </div>
                                </div>

                                <div class="mt-5 space-y-4 pl-10">
                                    <div class="space-y-2">
                                        <x-input-label :for="'q-'. $question->id" value="Pilih teman" />
                                        <x-form-select :id="'q-'. $question->id" name="answers[{{ $question->id }}][chosen_student_id]" required>
                                            <option value="">Pilih siswa</option>
                                            @foreach($students as $student)
                                                <option value="{{ $student->id }}" @selected(old("answers.{$question->id}.chosen_student_id", $answer?->chosen_student_id) == $student->id)>{{ $student->name }}</option>
                                            @endforeach
                                        </x-form-select>
                                        @error("answers.{$question->id}.chosen_student_id")<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                                    </div>

                                    <div class="space-y-2">
                                        <x-input-label :for="'alasan-'. $question->id" value="Alasan memilih / menolak (opsional)" />
                                        <textarea id="alasan-{{ $question->id }}" name="answers[{{ $question->id }}][reason]" rows="2" placeholder="Tulis alasan singkat..." class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/60 px-4 py-3 text-sm text-slate-900 dark:text-slate-100 shadow-xs focus:border-blue-400 focus:outline-hidden focus:ring-2 focus:ring-blue-100">{{ old("answers.{$question->id}.reason", $answer?->reason) }}</textarea>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endforeach

            <div class="flex justify-end">
                <x-primary-button class="rounded-full px-6 py-3">Simpan Jawaban</x-primary-button>
            </div>
        </form>
    @endif
</div>
@endsection
