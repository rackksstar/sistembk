@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
        <x-section-title title="Instrumen Asesmen" description="Isi instrumen minat bakat, strategi belajar, dan kepribadian sesuai kondisi diri." />
        <x-alert class="mt-5" type="success" :message="session('success')" />

        <div class="mt-6 flex flex-wrap gap-2">
            @foreach($categories as $value => $label)
                <a href="{{ route('siswa.instruments.index', ['category' => $value]) }}" class="rounded-full px-4 py-2 text-sm font-semibold transition {{ $category === $value ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/20' : 'border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <div class="mt-5 grid gap-3 md:grid-cols-3">
            @foreach($categories as $value => $label)
                @php($submission = $latestSubmissions[$value] ?? null)
                <div class="rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/60 p-4">
                    <p class="text-sm font-semibold text-slate-950 dark:text-white">{{ $label }}</p>
                    <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">
                        {{ $submission ? $submission->result_label.' - '.$submission->submitted_at?->format('d M Y') : 'Belum diisi' }}
                    </p>
                    @if($submission && $value === \App\Models\InstrumentQuestion::CATEGORY_STRATEGI_BELAJAR)
                        <a href="{{ route('siswa.instruments.strategi-belajar-result') }}" class="mt-2 inline-block text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                            Lihat rincian hasil &rarr;
                        </a>
                    @elseif($submission && $value === \App\Models\InstrumentQuestion::CATEGORY_MINAT_BAKAT)
                        <a href="{{ route('siswa.instruments.minat-bakat-result') }}" class="mt-2 inline-block text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                            Lihat rincian hasil &rarr;
                        </a>
                    @endif
                </div>
            @endforeach
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
        @if($questions->isEmpty())
            <x-empty-state title="Soal belum tersedia" description="Guru BK belum mengaktifkan soal untuk kategori ini." />
        @elseif($category === \App\Models\InstrumentQuestion::CATEGORY_STRATEGI_BELAJAR)
            @include('siswa.instruments.partials.strategi-belajar-form')
        @else
            <form method="POST" action="{{ route('siswa.instruments.store') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="category" value="{{ $category }}">

                @foreach($questions as $question)
                    <div class="rounded-3xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/60 p-5">
                        <p class="font-semibold leading-7 text-slate-950 dark:text-white">{{ $loop->iteration }}. {{ $question->question }}</p>
                        <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-4">
                            <span class="text-xs font-medium text-slate-500 dark:text-slate-400 sm:w-32 sm:shrink-0 sm:text-right">
                                {{ $question->options[0]['label'] ?? '' }}
                            </span>
                            <div class="flex flex-1 items-start justify-between gap-2">
                                @foreach($question->options as $index => $option)
                                    <label class="flex cursor-pointer flex-col items-center gap-2 text-xs font-semibold text-slate-600 dark:text-slate-300">
                                        <span>{{ $index + 1 }}</span>
                                        <input
                                            type="radio"
                                            name="answers[{{ $question->id }}]"
                                            value="{{ $index }}"
                                            required
                                            @checked((string) old("answers.{$question->id}") === (string) $index)
                                            title="{{ $option['label'] }}"
                                            class="h-5 w-5 border-slate-300 dark:border-slate-600 text-blue-600 focus:ring-blue-500"
                                        >
                                    </label>
                                @endforeach
                            </div>
                            <span class="text-xs font-medium text-slate-500 dark:text-slate-400 sm:w-32 sm:shrink-0">
                                {{ $question->options[count($question->options) - 1]['label'] ?? '' }}
                            </span>
                        </div>
                    </div>
                @endforeach

                @error('answers')
                    <x-alert type="error" :message="$message" />
                @enderror

                <x-primary-button class="rounded-full px-6 py-3">Kirim dan Lihat Skor</x-primary-button>
            </form>
        @endif
    </section>
</div>
@endsection
