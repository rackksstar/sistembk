@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <x-section-title title="Instrumen Asesmen" description="Isi instrumen minat bakat, karier, akademik, kepribadian, sosiometri, dan masalah sesuai kondisi diri." />
        <x-alert class="mt-5" type="success" :message="session('success')" />

        <div class="mt-6 flex flex-wrap gap-2">
            @foreach($categories as $value => $label)
                <a href="{{ route('siswa.instruments.index', ['category' => $value]) }}" class="rounded-full px-4 py-2 text-sm font-semibold transition {{ $category === $value ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/20' : 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <div class="mt-5 grid gap-3 md:grid-cols-3">
            @foreach($categories as $value => $label)
                @php($submission = $latestSubmissions[$value] ?? null)
                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                    <p class="text-sm font-semibold text-slate-950">{{ $label }}</p>
                    <p class="mt-1 text-xs leading-5 text-slate-500">
                        @if($submission)
                            {{ $submission->result_label }} ({{ number_format($submission->percentage ?? 0, 2) }}%) - {{ $submission->submitted_at?->format('d M Y') }}
                        @else
                            Belum diisi
                        @endif
                    </p>
                </div>
            @endforeach
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        @if($questions->isEmpty())
            <x-empty-state title="Soal belum tersedia" description="Guru BK belum mengaktifkan soal untuk kategori ini." />
        @else
            <form method="POST" action="{{ route('siswa.instruments.store') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="category" value="{{ $category }}">

                @foreach($questions as $question)
                    <div class="rounded-3xl border border-slate-100 bg-slate-50 p-5">
                        <p class="font-semibold leading-7 text-slate-950">{{ $loop->iteration }}. {{ $question->question }}</p>
                        <div class="mt-4 grid gap-3 md:grid-cols-2">
                            @foreach($question->options as $index => $option)
                                <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-white bg-white p-4 text-sm font-medium text-slate-700 shadow-sm transition hover:border-blue-200">
                                    <input type="radio" name="answers[{{ $question->id }}]" value="{{ $index }}" required @checked((string) old("answers.{$question->id}") === (string) $index) class="border-slate-300 text-blue-600 focus:ring-blue-500">
                                    {{ $option['label'] }}
                                </label>
                            @endforeach
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
