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
    <div class="mb-5">
        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-600 dark:text-blue-400" x-text="'Bagian ' + section + ' dari ' + totalSections"></p>
        <h3 class="mt-1 text-lg font-bold text-slate-950 dark:text-white" x-text="sectionLabels[section]"></h3>
    </div>

    <form method="POST" action="{{ route('siswa.instruments.store') }}" x-ref="form" class="space-y-5">
        @csrf
        <input type="hidden" name="category" value="{{ $category }}">

        @foreach($sectionLabels as $sectionNum => $sectionLabel)
            <div class="space-y-5" x-show="section === {{ $sectionNum }}" x-cloak>
                @foreach($questionsBySection->get($sectionNum, collect()) as $question)
                    <div
                        class="rounded-3xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/60 p-5 transition"
                        :class="isLocked({{ $sectionNum }}) ? 'opacity-60' : ''"
                    >
                        <p class="font-semibold leading-7 text-slate-950 dark:text-white">{{ $loop->iteration }}. {{ $question->question }}</p>
                        <div
                            class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-4"
                            :class="isLocked({{ $sectionNum }}) ? 'pointer-events-none' : ''"
                        >
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
                                            x-model="answers[{{ $question->id }}]"
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
            </div>
        @endforeach

        @error('answers')
            <x-alert type="error" :message="$message" />
        @enderror

        <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
            <button
                type="button"
                x-show="section > 1"
                x-cloak
                x-on:click="incomplete = false; section--"
                class="inline-flex items-center justify-center rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-5 py-3 text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60"
            >Sebelumnya</button>

            <div class="ml-auto flex items-center gap-4">
                <span x-show="incomplete" x-cloak class="text-xs font-medium text-red-600 dark:text-red-400">Jawab semua pertanyaan pada bagian ini terlebih dahulu.</span>

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
                >Simpan</button>
            </div>
        </div>
    </form>

    <div x-show="showConfirm" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
        <div x-on:click.outside="showConfirm = false" class="w-full max-w-sm rounded-3xl bg-white dark:bg-slate-900 p-6 text-center shadow-2xl">
            <div class="flex justify-end">
                <button type="button" x-on:click="showConfirm = false" class="rounded-full bg-slate-100 dark:bg-slate-800 px-3 py-1 text-sm font-semibold text-slate-600 dark:text-slate-400">x</button>
            </div>
            <p class="text-lg font-bold text-slate-950 dark:text-white">Sudah yakin dengan jawabanmu?</p>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Jawaban yang sudah kamu simpan tidak bisa diubah lagi.</p>
            <div class="mt-6 flex items-center justify-center gap-3">
                <button type="button" x-on:click="showConfirm = false" class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-5 py-3 text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60">Batal</button>
                <button type="button" x-on:click="confirmSection()" class="rounded-2xl bg-orange-500 px-5 py-3 text-sm font-semibold text-white hover:bg-orange-400">Simpan</button>
            </div>
        </div>
    </div>
</div>
