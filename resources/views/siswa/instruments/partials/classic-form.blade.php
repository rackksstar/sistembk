@php
    $sections = collect($sections ?? []);
    if ($sections->isEmpty()) {
        $sections = collect([['title' => null, 'questions' => $questions]]);
    }
    $sectionTotal = max($sections->count(), 1);
    $questionNumber = 0;
@endphp

<form method="POST" action="{{ route('siswa.instruments.store') }}" class="space-y-8">
    @csrf
    <input type="hidden" name="category" value="{{ $category }}">

    @foreach($sections as $sectionIndex => $section)
        @php
            $sectionQuestions = collect($section['questions'] ?? []);
            $sectionTitle = $section['title'] ?? null;
        @endphp

        <div class="space-y-6">
            @if($sectionTitle || $sectionTotal > 1)
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-blue-600 dark:text-blue-300">
                        BAGIAN {{ $sectionIndex + 1 }} DARI {{ $sectionTotal }}
                    </p>
                    @if($sectionTitle)
                        <h3 class="mt-2 text-xl font-bold tracking-tight text-slate-950 dark:text-white">{{ $sectionTitle }}</h3>
                    @endif
                </div>
            @endif

            @foreach($sectionQuestions as $question)
                @php
                    $questionNumber++;
                    $options = collect($question->options ?? [])
                        ->filter(fn ($option) => is_array($option) && array_key_exists('label', $option))
                        ->values()
                        ->all();
                    $firstLabel = $options[0]['label'] ?? 'Sangat Tidak Sesuai';
                    $lastLabel = $options[count($options) - 1]['label'] ?? 'Sangat Sesuai';
                    $isLikert = count($options) >= 3;
                @endphp

                <div class="space-y-5">
                    <p class="text-[15px] font-semibold leading-7 text-slate-950 dark:text-white">
                        {{ $questionNumber }}. {{ $question->question }}
                    </p>

                    @if($isLikert)
                        <div class="mx-auto w-full max-w-2xl px-1 sm:px-6">
                            <div class="grid items-start gap-x-2 sm:gap-x-4" style="grid-template-columns: repeat({{ count($options) }}, minmax(0, 1fr));">
                                @foreach($options as $index => $option)
                                    <label class="flex cursor-pointer flex-col items-center gap-2.5">
                                        <span class="text-sm font-semibold tabular-nums text-slate-700 dark:text-slate-300">{{ $index + 1 }}</span>
                                        <input
                                            type="radio"
                                            name="answers[{{ $question->id }}]"
                                            value="{{ $index }}"
                                            required
                                            @checked((string) old('answers.'.$question->id) === (string) $index)
                                            class="h-5 w-5 cursor-pointer border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:checked:bg-blue-500 dark:focus:ring-blue-400"
                                            title="{{ $option['label'] }}"
                                            aria-label="{{ ($index + 1).'. '.$option['label'] }}"
                                        >
                                    </label>
                                @endforeach
                            </div>

                            <div class="mt-3 grid gap-x-2 sm:gap-x-4" style="grid-template-columns: repeat({{ count($options) }}, minmax(0, 1fr));">
                                <p class="col-start-1 text-left text-[11px] font-medium leading-4 text-slate-500 dark:text-slate-400">
                                    {{ $firstLabel }}
                                </p>
                                <p class="text-right text-[11px] font-medium leading-4 text-slate-500 dark:text-slate-400" style="grid-column-start: {{ count($options) }};">
                                    {{ $lastLabel }}
                                </p>
                            </div>
                        </div>
                    @else
                        <div class="grid gap-3 md:grid-cols-2">
                            @foreach($options as $index => $option)
                                <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-slate-100 bg-slate-50 p-4 text-sm font-medium text-slate-700 transition hover:border-blue-200 dark:border-slate-800 dark:bg-slate-800/60 dark:text-slate-300 dark:hover:border-blue-800">
                                    <input
                                        type="radio"
                                        name="answers[{{ $question->id }}]"
                                        value="{{ $index }}"
                                        required
                                        @checked((string) old('answers.'.$question->id) === (string) $index)
                                        class="border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800"
                                    >
                                    {{ $option['label'] }}
                                </label>
                            @endforeach
                        </div>
                    @endif
                </div>

                @if(! $loop->last)
                    <div class="border-t border-slate-100 dark:border-slate-800"></div>
                @endif
            @endforeach
        </div>

        @if(! $loop->last)
            <div class="border-t border-slate-200 dark:border-slate-700"></div>
        @endif
    @endforeach

    @error('answers')
        <x-alert type="error" :message="$message" />
    @enderror

    <div class="pt-2">
        <x-primary-button class="rounded-full px-6 py-3">Kirim dan Lihat Skor</x-primary-button>
    </div>
</form>
