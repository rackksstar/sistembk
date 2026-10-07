@php
    $questionPayload = $questions->values()->map(function ($q, $i) use ($oldAnswers) {
        return [
            'id' => $q->id,
            'number' => $i + 1,
            'text' => $q->question,
            'options' => collect($q->options)->values()->map(function ($opt, $idx) {
                return [
                    'index' => $idx,
                    'label' => $opt['label'] ?? '',
                ];
            })->all(),
            'answer' => $oldAnswers->has($q->id) ? (string) $oldAnswers[$q->id] : null,
        ];
    })->all();
    $startStep = $errors->any() || $oldAnswers->isNotEmpty() ? 'quiz' : 'intro';
    $requireJenjangChooser = ! empty($needsJenjangChooser);
@endphp

<div
    class="mx-auto max-w-2xl"
    x-data="minatWizard({
        step: @js($startStep),
        questions: @js($questionPayload),
        jenjang: @js(old('jenjang', $jenjang)),
        requireJenjang: @js($requireJenjangChooser),
    })"
>
    <div x-show="step === 'intro'" x-cloak class="space-y-5">
        <x-section-title
            title="Asesmen Minat Bakat"
            description="Jawab sejujurnya: seberapa tertarik kamu pada tiap kegiatan. Tidak ada jawaban benar/salah."
        />

        <ol class="space-y-3 text-sm text-slate-600 dark:text-slate-300">
            <li class="flex gap-3 rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/60 p-4">
                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-blue-600 text-xs font-bold text-white">1</span>
                <span>Isi <strong class="text-slate-900 dark:text-white" x-text="total"></strong> soal, satu per satu (skala Sangat tidak tertarik → Sangat tertarik).</span>
            </li>
            <li class="flex gap-3 rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/60 p-4">
                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-blue-600 text-xs font-bold text-white">2</span>
                <span>Sistem menghitung skor per kategori minat dan membuat <strong class="text-slate-900 dark:text-white">Kode Minat</strong> (3 huruf teratas).</span>
            </li>
            <li class="flex gap-3 rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/60 p-4">
                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-blue-600 text-xs font-bold text-white">3</span>
                <span>
                    @if($jenjang === 'SMK')
                        Kamu mendapat rekomendasi <strong class="text-slate-900 dark:text-white">bidang karier</strong> sesuai minat.
                    @else
                        Kamu mendapat rekomendasi <strong class="text-slate-900 dark:text-white">program studi PCR</strong> sesuai minat.
                    @endif
                </span>
            </li>
        </ol>

        @if($requireJenjangChooser)
            <div class="rounded-2xl border border-blue-100 dark:border-blue-900/50 bg-blue-50/70 dark:bg-blue-950/30 p-4 text-left">
                <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100" for="jenjang_intro">Jenjang asesmen</label>
                <select
                    id="jenjang_intro"
                    class="ui-input js-select2 mt-2"
                    data-placeholder="Pilih jenjang"
                    x-model="jenjang"
                >
                    <option value="">Pilih jenjang</option>
                    <option value="SMA">SMA</option>
                    <option value="SMK">SMK</option>
                </select>
            </div>
        @elseif($jenjang)
            <p class="text-sm text-slate-500 dark:text-slate-400">
                Jenjang: <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $jenjang }}</span>
            </p>
        @endif

        <button
            type="button"
            class="w-full rounded-full bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-500 disabled:cursor-not-allowed disabled:opacity-50"
            x-on:click="start()"
            x-bind:disabled="requireJenjang && !jenjang"
        >
            Mulai asesmen
        </button>
    </div>

    <form
        method="POST"
        action="{{ route('siswa.instruments.store') }}"
        x-show="step === 'quiz'"
        x-cloak
        class="space-y-5"
        x-on:submit="if (!allAnswered) { $event.preventDefault(); alert('Masih ada soal yang belum dijawab.') }"
    >
        @csrf
        <input type="hidden" name="category" value="{{ $category }}">
        <input type="hidden" name="jenjang" x-bind:value="jenjang" x-bind:disabled="!jenjang">

        <template x-for="q in questions" :key="q.id">
            <input
                type="hidden"
                x-bind:name="'answers[' + q.id + ']'"
                x-bind:value="q.answer !== null && q.answer !== undefined ? q.answer : ''"
                x-bind:disabled="q.answer === null || q.answer === undefined || q.answer === ''"
            >
        </template>

        <div class="space-y-3">
            <div class="flex items-center justify-between gap-3">
                <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">
                    Soal <span x-text="index + 1"></span> dari <span x-text="total"></span>
                </p>
                <p class="text-sm font-bold text-blue-700 dark:text-blue-300" x-text="answeredCount + '/' + total + ' terjawab'"></p>
            </div>
            <div class="h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                <div class="h-full rounded-full bg-blue-600 transition-all duration-300" x-bind:style="'width:' + progress + '%'"></div>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/60 p-5 sm:p-6" x-show="current">
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Minat Bakat</p>
            <p class="mt-2 text-lg font-semibold leading-8 text-slate-950 dark:text-white" x-text="currentText"></p>

            <div class="mt-5 grid gap-3">
                <template x-for="opt in currentOptions" :key="opt.index">
                    <button
                        type="button"
                        class="flex w-full items-center gap-3 rounded-2xl border bg-white p-4 text-left text-sm font-medium shadow-sm transition dark:bg-slate-900"
                        x-bind:class="isSelected(opt.index)
                            ? 'border-blue-500 ring-2 ring-blue-100 dark:ring-blue-900/50 text-blue-800 dark:text-blue-200'
                            : 'border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:border-blue-200 dark:hover:border-blue-800'"
                        x-on:click="select(opt.index)"
                    >
                        <span
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-bold"
                            x-bind:class="isSelected(opt.index) ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400'"
                            x-text="opt.index + 1"
                        ></span>
                        <span x-text="opt.label"></span>
                    </button>
                </template>
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3">
            <button
                type="button"
                class="rounded-full border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800/60"
                x-on:click="prev()"
                x-bind:disabled="index === 0"
                x-bind:class="index === 0 && 'opacity-40 cursor-not-allowed'"
            >
                Sebelumnya
            </button>

            <div class="flex flex-wrap gap-2">
                <button
                    type="button"
                    class="rounded-full border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800/60 disabled:cursor-not-allowed disabled:opacity-40"
                    x-show="index < total - 1"
                    x-on:click="next()"
                    x-bind:disabled="!currentAnswered"
                >
                    Berikutnya
                </button>

                <button
                    type="submit"
                    class="rounded-full bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-500 disabled:cursor-not-allowed disabled:opacity-50"
                    x-show="index === total - 1"
                    x-bind:disabled="!allAnswered"
                >
                    Kirim dan Lihat Hasil
                </button>
            </div>
        </div>

        <p class="text-center text-xs text-slate-400" x-show="!currentAnswered">
            Pilih salah satu opsi di atas sebelum lanjut ke soal berikutnya.
        </p>

        @error('answers')
            <x-alert type="error" :message="$message" />
        @enderror

        <p class="text-center text-xs text-slate-400">
            Kamu bisa kembali ke soal sebelumnya kapan saja. Hasil terbaru yang ditampilkan setelah kirim.
        </p>
    </form>
</div>
