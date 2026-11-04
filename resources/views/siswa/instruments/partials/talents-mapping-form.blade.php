{{-- Form Likert Talents Mapping — selaras sistembk-main (semua soal di satu halaman) --}}
@php
    $submitLabel = $submitLabel ?? 'Kirim dan Lihat Skor';
    $accent = $accent ?? 'emerald'; // emerald = kerja, blue = kuliah (jika dipakai)
    $btnClass = $accent === 'blue'
        ? 'rounded-full bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-500'
        : 'rounded-full bg-emerald-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-500/20 transition hover:bg-emerald-500';
@endphp

<form method="POST" action="{{ route('siswa.instruments.store') }}" class="space-y-5">
    @csrf
    <input type="hidden" name="category" value="{{ $category }}">
    @if(! empty($jenjang))
        <input type="hidden" name="jenjang" value="{{ $jenjang }}">
    @endif

    <div class="rounded-2xl border border-emerald-100 bg-emerald-50/60 px-4 py-3 text-sm leading-6 text-slate-700 dark:border-emerald-900/40 dark:bg-emerald-950/20 dark:text-slate-300">
        <p class="font-semibold text-slate-950 dark:text-white">Talents Mapping (RIASEC)</p>
        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
            Ada {{ $questions->count() }} kegiatan. Pilih seberapa suka kamu pada masing-masing (1 = Sangat Tidak Suka → 5 = Sangat Suka).
        </p>
    </div>

    @foreach($questions as $question)
        @php
            $options = collect($question->options ?? [])
                ->filter(fn ($option) => is_array($option) && array_key_exists('label', $option))
                ->values()
                ->all();
            $optionCount = max(count($options), 1);
            $firstLabel = $options[0]['label'] ?? 'Sangat Tidak Suka';
            $lastLabel = $options[$optionCount - 1]['label'] ?? 'Sangat Suka';
        @endphp

        <div class="rounded-3xl border border-slate-100 bg-slate-50/80 p-5 dark:border-slate-800 dark:bg-slate-800/50">
            <p class="font-semibold leading-7 text-slate-950 dark:text-white">
                {{ $loop->iteration }}. {{ $question->question }}
            </p>
            <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-4">
                <span class="text-xs font-medium text-slate-500 dark:text-slate-400 sm:w-32 sm:shrink-0 sm:text-right">
                    {{ $firstLabel }}
                </span>
                <div class="flex flex-1 items-start justify-between gap-2">
                    @foreach($options as $index => $option)
                        <label class="group flex cursor-pointer flex-col items-center gap-2 text-xs font-semibold text-slate-600 dark:text-slate-300">
                            <span class="tabular-nums transition group-has-[:checked]:text-emerald-700 dark:group-has-[:checked]:text-emerald-300">{{ $index + 1 }}</span>
                            <span class="flex h-9 w-9 items-center justify-center rounded-full border-2 border-slate-200 bg-white transition group-hover:border-emerald-300 group-has-[:checked]:border-emerald-600 group-has-[:checked]:bg-emerald-600 group-has-[:checked]:shadow-md group-has-[:checked]:shadow-emerald-500/25 dark:border-slate-600 dark:bg-slate-900 dark:group-has-[:checked]:border-emerald-500 dark:group-has-[:checked]:bg-emerald-600">
                                <input
                                    type="radio"
                                    name="answers[{{ $question->id }}]"
                                    value="{{ $index }}"
                                    required
                                    @checked((string) old("answers.{$question->id}") === (string) $index)
                                    title="{{ $option['label'] }}"
                                    aria-label="{{ ($index + 1).'. '.$option['label'] }}"
                                    class="sr-only"
                                >
                                <span class="h-2 w-2 rounded-full bg-transparent transition group-has-[:checked]:bg-white"></span>
                            </span>
                        </label>
                    @endforeach
                </div>
                <span class="text-xs font-medium text-slate-500 dark:text-slate-400 sm:w-32 sm:shrink-0">
                    {{ $lastLabel }}
                </span>
            </div>
        </div>
    @endforeach

    @error('answers')
        <x-alert type="error" :message="$message" />
    @enderror
    @error('jenjang')
        <x-alert type="error" :message="$message" />
    @enderror

    <button type="submit" class="{{ $btnClass }}">{{ $submitLabel }}</button>
</form>
