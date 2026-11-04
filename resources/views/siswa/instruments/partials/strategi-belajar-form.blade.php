@php
    $sectionLabels = \App\Models\InstrumentQuestion::SECTIONS[\App\Models\InstrumentQuestion::CATEGORY_STRATEGI_BELAJAR];
    $questionsBySection = $questions->groupBy('section');

    $sectionIds = collect($sectionLabels)->keys()->mapWithKeys(
        fn ($num) => [$num => $questionsBySection->get($num, collect())->pluck('id')->values()]
    );

    $initialAnswers = $questions->mapWithKeys(function ($question) {
        $old = old("answers.{$question->id}");

        return [$question->id => $old !== null ? (string) $old : null];
    });
@endphp

<div
    x-data="{
        section: 1,
        totalSections: {{ count($sectionLabels) }},
        confirmedUpTo: 0,
        showConfirm: false,
        incomplete: false,
        answers: {{ \Illuminate\Support\Js::from($initialAnswers) }},
        sectionIds: {{ \Illuminate\Support\Js::from($sectionIds) }},
        sectionLabels: {{ \Illuminate\Support\Js::from($sectionLabels) }},
        isSectionComplete(section) {
            return this.sectionIds[section].every((id) => this.answers[id] !== null && this.answers[id] !== undefined);
        },
        isLocked(section) {
            return section <= this.confirmedUpTo;
        },
        attemptAdvance() {
            if (! this.isSectionComplete(this.section)) {
                this.incomplete = true;
                return;
            }
            this.incomplete = false;
            this.showConfirm = true;
        },
        confirmSection() {
            this.showConfirm = false;
            this.confirmedUpTo = Math.max(this.confirmedUpTo, this.section);
            if (this.section < this.totalSections) {
                this.section++;
            } else {
                this.$refs.form.submit();
            }
        },
    }"
>
    <div class="mb-6 space-y-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-600 dark:text-blue-400" x-text="'Bagian ' + section + ' dari ' + totalSections"></p>
            <h3 class="mt-1 text-lg font-bold text-slate-950 dark:text-white" x-text="sectionLabels[section]"></h3>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                Jawab semua soal pada bagian ini, lalu simpan untuk lanjut. Jawaban tersimpan tidak bisa diubah.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <template x-for="n in totalSections" :key="n">
                <button
                    type="button"
                    class="flex flex-1 flex-col items-center gap-1.5"
                    x-on:click="if (n <= confirmedUpTo + 1 || n <= section) { incomplete = false; section = n }"
                    x-bind:disabled="n > confirmedUpTo + 1 && n > section"
                    x-bind:class="(n > confirmedUpTo + 1 && n > section) ? 'cursor-not-allowed opacity-40' : 'cursor-pointer'"
                >
                    <span
                        class="flex h-8 w-8 items-center justify-center rounded-full text-xs font-bold transition"
                        x-bind:class="n <= confirmedUpTo
                            ? 'bg-emerald-600 text-white'
                            : (n === section ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/25' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400')"
                        x-text="n <= confirmedUpTo ? '✓' : n"
                    ></span>
                    <span
                        class="hidden text-[10px] font-semibold sm:block"
                        x-bind:class="n === section ? 'text-blue-700 dark:text-blue-300' : 'text-slate-400'"
                        x-text="sectionLabels[n]"
                    ></span>
                </button>
            </template>
        </div>
    </div>

    <form method="POST" action="{{ route('siswa.instruments.store') }}" x-ref="form" class="space-y-5">
        @csrf
        <input type="hidden" name="category" value="{{ $category }}">

        @foreach($sectionLabels as $sectionNum => $sectionLabel)
            <div class="space-y-5" x-show="section === {{ $sectionNum }}" x-cloak>
                @foreach($questionsBySection->get($sectionNum, collect()) as $question)
                    @php
                        $options = collect($question->options ?? [])
                            ->filter(fn ($option) => is_array($option) && array_key_exists('label', $option))
                            ->values()
                            ->all();
                        $optionCount = max(count($options), 1);
                        $firstLabel = $options[0]['label'] ?? 'Sangat Tidak Sesuai';
                        $lastLabel = $options[$optionCount - 1]['label'] ?? 'Sangat Sesuai';
                    @endphp
                    <div
                        class="rounded-3xl border border-slate-100 bg-slate-50/80 p-5 sm:p-6 transition dark:border-slate-800 dark:bg-slate-800/50"
                        :class="isLocked({{ $sectionNum }}) ? 'opacity-60' : ''"
                    >
                        <p class="text-[15px] font-semibold leading-7 text-slate-950 dark:text-white">
                            {{ $loop->iteration }}. {{ $question->question }}
                        </p>
                        <div
                            class="mx-auto mt-5 w-full max-w-2xl px-1 sm:px-4"
                            :class="isLocked({{ $sectionNum }}) ? 'pointer-events-none' : ''"
                        >
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
                                                x-model="answers[{{ $question->id }}]"
                                                title="{{ $option['label'] }}"
                                                aria-label="{{ ($index + 1).'. '.$option['label'] }}"
                                                class="sr-only"
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
                    </div>
                @endforeach
            </div>
        @endforeach

        @error('answers')
            <x-alert type="error" :message="$message" />
        @enderror

        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-5 dark:border-slate-800">
            <button
                type="button"
                x-show="section > 1"
                x-cloak
                x-on:click="incomplete = false; section--"
                class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800/60"
            >Sebelumnya</button>

            <div class="ml-auto flex flex-wrap items-center justify-end gap-3">
                <span x-show="incomplete" x-cloak class="text-xs font-medium text-red-600 dark:text-red-400">
                    Jawab semua pertanyaan pada bagian ini terlebih dahulu.
                </span>

                <button
                    type="button"
                    x-show="section <= confirmedUpTo"
                    x-cloak
                    x-on:click="section++"
                    class="rounded-full bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-500"
                >Selanjutnya</button>

                <button
                    type="button"
                    x-show="section > confirmedUpTo"
                    x-cloak
                    x-on:click="attemptAdvance()"
                    class="rounded-full bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-500"
                >
                    <span x-text="section === totalSections ? 'Kirim dan Lihat Hasil' : 'Simpan Bagian'"></span>
                </button>
            </div>
        </div>
    </form>

    <div x-show="showConfirm" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-[2px]">
        <div
            x-on:click.outside="showConfirm = false"
            role="dialog"
            aria-modal="true"
            aria-labelledby="strategi-confirm-title"
            class="w-full max-w-sm rounded-3xl border border-slate-100 bg-white p-6 text-center shadow-2xl dark:border-slate-800 dark:bg-slate-900"
        >
            <div class="flex justify-end">
                <button
                    type="button"
                    x-on:click="showConfirm = false"
                    aria-label="Tutup"
                    class="rounded-full bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-400"
                >×</button>
            </div>
            <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-300">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-6 w-6" aria-hidden="true">
                    <path d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <p id="strategi-confirm-title" class="text-lg font-bold text-slate-950 dark:text-white">Sudah yakin dengan jawabanmu?</p>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Jawaban yang sudah kamu simpan tidak bisa diubah lagi.</p>
            <div class="mt-6 flex items-center justify-center gap-3">
                <button type="button" x-on:click="showConfirm = false" class="rounded-full border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800/60">Batal</button>
                <button type="button" x-on:click="confirmSection()" class="rounded-full bg-orange-500 px-5 py-3 text-sm font-semibold text-white transition hover:bg-orange-400">Simpan</button>
            </div>
        </div>
    </div>
</div>
