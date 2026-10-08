{{-- Form skala Likert Yola (1–5) — selaras gaya strategi belajar / classic --}}
@php
    $categoryLabel = \App\Models\InstrumentQuestion::YOLA_CATEGORIES[$category]
        ?? \App\Models\InstrumentQuestion::CATEGORIES[$category]
        ?? 'Instrumen';
@endphp

<form method="POST" action="{{ route('siswa.instruments.store') }}" class="space-y-6">
    @csrf
    <input type="hidden" name="category" value="{{ $category }}">

    <div class="rounded-2xl border border-emerald-100 bg-emerald-50/60 px-4 py-3 text-sm leading-6 text-slate-700 dark:border-emerald-900/40 dark:bg-emerald-950/20 dark:text-slate-300">
        <p class="font-semibold text-slate-950 dark:text-white">{{ $categoryLabel }}</p>
        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
            Jawab sejujurnya pada skala 1–5. Tidak ada jawaban benar atau salah.
        </p>
    </div>

    @foreach($questions as $question)
        @php
            $options = collect($question->options ?? [])
                ->filter(fn ($option) => is_array($option) && array_key_exists('label', $option))
                ->values()
                ->all();
            $optionCount = max(count($options), 1);
            $firstLabel = $options[0]['label'] ?? 'Sangat Tidak Sesuai';
            $lastLabel = $options[$optionCount - 1]['label'] ?? 'Sangat Sesuai';
            $isLikert = count($options) >= 3;
        @endphp

        <div class="rounded-3xl border border-slate-100 bg-slate-50/80 p-5 sm:p-6 dark:border-slate-800 dark:bg-slate-800/50">
            <p class="text-[15px] font-semibold leading-7 text-slate-950 dark:text-white">
                {{ $loop->iteration }}. {{ $question->question }}
            </p>

            @if($isLikert)
                <div class="mx-auto mt-5 w-full max-w-2xl px-1 sm:px-4">
                    <div
                        class="grid items-start gap-x-2 sm:gap-x-3"
                        style="grid-template-columns: repeat({{ $optionCount }}, minmax(0, 1fr));"
                    >
                        @foreach($options as $index => $option)
                            <label class="group flex cursor-pointer flex-col items-center gap-2.5">
                                <span class="text-sm font-semibold tabular-nums text-slate-600 transition group-has-[:checked]:text-blue-700 dark:text-slate-300 dark:group-has-[:checked]:text-blue-300">
                                    {{ $index + 1 }}
                                </span>
                                <span class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-slate-200 bg-white transition group-hover:border-blue-300 group-has-[:checked]:border-blue-600 group-has-[:checked]:bg-blue-600 group-has-[:checked]:shadow-md group-has-[:checked]:shadow-blue-500/25 dark:border-slate-600 dark:bg-slate-900 dark:group-hover:border-blue-500 dark:group-has-[:checked]:border-blue-500 dark:group-has-[:checked]:bg-blue-600">
                                    <input
                                        type="radio"
                                        name="answers[{{ $question->id }}]"
                                        value="{{ $index }}"
                                        required
                                        @checked((string) old("answers.{$question->id}") === (string) $index)
                                        class="sr-only"
                                        title="{{ $option['label'] }}"
                                        aria-label="{{ ($index + 1).'. '.$option['label'] }}"
                                    >
                                    <span class="h-2.5 w-2.5 rounded-full bg-transparent transition group-has-[:checked]:bg-white"></span>
                                </span>
                            </label>
                        @endforeach
                    </div>

                    <div
                        class="mt-3 grid gap-x-2 sm:gap-x-3"
                        style="grid-template-columns: repeat({{ $optionCount }}, minmax(0, 1fr));"
                    >
                        <p class="col-start-1 text-left text-[11px] font-medium leading-4 text-slate-500 dark:text-slate-400">
                            {{ $firstLabel }}
                        </p>
                        <p class="text-right text-[11px] font-medium leading-4 text-slate-500 dark:text-slate-400" style="grid-column-start: {{ $optionCount }};">
                            {{ $lastLabel }}
                        </p>
                    </div>
                </div>
            @else
                <div class="mt-4 grid gap-3 md:grid-cols-2">
                    @foreach($options as $index => $option)
                        <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-white bg-white p-4 text-sm font-medium text-slate-700 shadow-sm transition hover:border-blue-200 has-[:checked]:border-blue-500 has-[:checked]:ring-2 has-[:checked]:ring-blue-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-blue-700 dark:has-[:checked]:ring-blue-900/50">
                            <input
                                type="radio"
                                name="answers[{{ $question->id }}]"
                                value="{{ $index }}"
                                required
                                @checked((string) old("answers.{$question->id}") === (string) $index)
                                class="border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800"
                            >
                            {{ $option['label'] }}
                        </label>
                    @endforeach
                </div>
            @endif
        </div>
    @endforeach

    @error('answers')
        <x-alert type="error" :message="$message" />
    @enderror

    <div class="flex flex-wrap items-center gap-3 pt-1">
        <x-primary-button class="rounded-full px-6 py-3">Kirim dan Lihat Skor</x-primary-button>
        <p class="text-xs text-slate-400">Pastikan semua soal sudah dijawab sebelum mengirim.</p>
    </div>
</form>
