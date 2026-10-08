@php
    $formKey = $question?->id ?? 'new';
    $yolaLikert = [
        ['label' => 'Sangat Tidak Sesuai', 'score' => 1],
        ['label' => 'Tidak Sesuai', 'score' => 2],
        ['label' => 'Cukup Sesuai', 'score' => 3],
        ['label' => 'Sesuai', 'score' => 4],
        ['label' => 'Sangat Sesuai', 'score' => 5],
    ];
    $mbtiAxes = $mbtiAxes ?? \App\Support\Mbti::AXES;
    $isKepribadianForm = old('category', $question?->category) === \App\Models\InstrumentQuestion::CATEGORY_KEPRIBADIAN;

    // Dimensi + kutub tersimpan di kolom (format Likert ala 16Personalities).
    $selectedAxis = old('mbti_axis', $question?->mbti_axis ?? array_key_first($mbtiAxes));
    $selectedPole = old('mbti_pole', $question?->mbti_pole ?? '');

    $defaultOptions = (($module ?? 'yola') === 'key')
        ? \App\Models\InstrumentQuestion::TALENTS_LIKERT_OPTIONS
        : $yolaLikert;
    $initialOptions = collect(old('options', $question?->options ?? $defaultOptions))
        ->map(fn ($option) => [
            'label' => $option['label'] ?? '',
            'score' => (int) ($option['score'] ?? 0),
        ])
        ->values()
        ->all();
    $initialTalent = old('talent_code', $question?->talent_code
        ?? $interestCategories->firstWhere('id', old('interest_category_id', $question?->interest_category_id))?->kode);
    $strategiSections = \App\Models\InstrumentQuestion::SECTIONS[\App\Models\InstrumentQuestion::CATEGORY_GAYA_BELAJAR] ?? [];
@endphp

<div
    class="space-y-4"
    x-data="{
        category: @js(old('category', $question?->category ?? (($module ?? 'yola') === 'key' ? 'minat_bakat' : 'minat_kerja'))),
        talentCode: @js($initialTalent),
        section: @js(old('section', $question?->section)),
        interestCategories: @js($interestCategories->map(fn ($c) => ['id' => $c->id, 'kode' => $c->kode, 'nama' => $c->nama])->values()),
        jenjangTarget: @js(old('jenjang_target', $question?->jenjang_target ?? 'semua')),
        bobot: @js((int) old('bobot', $question?->bobot ?? 1)),
        options: @js($initialOptions),
        axis: @js($selectedAxis),
        pole: @js($selectedPole),
        axes: @js($mbtiAxes),
        likertTemplate: @js($defaultOptions),
        get isMinatBakat() {
            return this.category === 'minat_bakat';
        },
        get isKepribadian() {
            return this.category === 'kepribadian';
        },
        get isStrategiBelajar() {
            return this.category === 'gaya_belajar';
        },
        get interestCategoryId() {
            const match = this.interestCategories.find((item) => item.kode === this.talentCode);
            return match ? match.id : '';
        },
        addOption() {
            if (this.options.length < 6) {
                this.options.push({ label: '', score: 0 });
            }
        },
        removeOption(index) {
            if (this.options.length > 2) {
                this.options.splice(index, 1);
            }
        },
        applyLikert() {
            this.options = this.likertTemplate.map((option) => ({ ...option }));
        },
        init() {
            const refresh = () => {
                this.$nextTick(() => {
                    if (typeof window.refreshSelect2 === 'function') {
                        window.refreshSelect2(this.$root);
                    }
                });
            };
            this.$watch('category', (value) => {
                if (value !== 'gaya_belajar') {
                    this.section = null;
                }
                refresh();
            });
            this.$watch('isMinatBakat', refresh);
            this.$watch('isStrategiBelajar', refresh);
            this.$watch('isKepribadian', refresh);
            refresh();
        },
    }"
>
    <div class="space-y-2">
        <x-input-label for="category_{{ $formKey }}" value="Jenis Instrumen" />
        <x-form-select id="category_{{ $formKey }}" name="category" x-model="category" required>
            <option value="">Pilih kategori</option>
            @foreach($categories as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </x-form-select>
        <p class="text-xs text-slate-500 dark:text-slate-400">Kategori "Kepribadian" memakai pernyataan skala Likert ala 16Personalities, kategori lain memakai skor Likert seperti biasa.</p>
    </div>

    <div class="space-y-2">
        <x-input-label for="question_{{ $formKey }}" value="Soal" />
        <textarea id="question_{{ $formKey }}" name="question" rows="4" required class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm text-slate-900 dark:text-slate-100 shadow-xs focus:border-blue-400 focus:outline-hidden focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/50">{{ old('question', $question?->question) }}</textarea>
    </div>

    <div x-show="isStrategiBelajar" x-cloak class="space-y-2 rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/40">
        <x-input-label for="section_{{ $formKey }}" value="Bagian Strategi Belajar" />
        <x-form-select
            id="section_{{ $formKey }}"
            name="section"
            x-model="section"
            x-bind:required="isStrategiBelajar"
            x-bind:disabled="!isStrategiBelajar"
        >
            <option value="">Pilih bagian</option>
            @foreach($strategiSections as $num => $label)
                <option value="{{ $num }}">Bagian {{ $num }}: {{ $label }}</option>
            @endforeach
        </x-form-select>
        <p class="text-xs text-slate-500 dark:text-slate-400">Wajib diisi agar wizard siswa (3 bagian) dan hasil per-bagian tampil benar.</p>
    </div>

    <div x-show="isMinatBakat" x-cloak class="space-y-4 rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/40 p-4">
        <div class="space-y-2">
            <x-input-label for="talent_code_{{ $formKey }}" value="Kode RIASEC (Talents Mapping)" />
            <x-form-select
                id="talent_code_{{ $formKey }}"
                name="talent_code"
                x-model="talentCode"
                x-bind:required="isMinatBakat"
            >
                <option value="">Pilih kode RIASEC</option>
                @foreach(\App\Models\InstrumentQuestion::RIASEC_CODES as $code => $label)
                    <option value="{{ $code }}">{{ $code }} — {{ $label }}</option>
                @endforeach
            </x-form-select>
            <input type="hidden" name="interest_category_id" :value="interestCategoryId">
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
            <div class="space-y-2">
                <x-input-label for="jenjang_target_{{ $formKey }}" value="Target Jenjang" />
                <x-form-select
                    id="jenjang_target_{{ $formKey }}"
                    name="jenjang_target"
                    x-model="jenjangTarget"
                    x-bind:required="isMinatBakat"
                >
                    @foreach($jenjangTargets as $target)
                        <option value="{{ $target }}">{{ $target === 'semua' ? 'Semua' : $target }}</option>
                    @endforeach
                </x-form-select>
            </div>

            <div class="space-y-2">
                <x-input-label for="bobot_{{ $formKey }}" value="Bobot Soal (1–5)" />
                <x-text-input
                    id="bobot_{{ $formKey }}"
                    name="bobot"
                    type="number"
                    min="1"
                    max="5"
                    x-model.number="bobot"
                    x-bind:required="isMinatBakat"
                />
            </div>
        </div>
    </div>

    <div x-show="isKepribadian" x-cloak class="space-y-3 rounded-2xl border border-blue-200 dark:border-blue-900 bg-blue-50/60 dark:bg-blue-950/30 p-4">
        <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">Format Tes Kepribadian (ala 16Personalities)</p>
        <p class="text-xs text-slate-600 dark:text-slate-400">Tulis satu pernyataan, pilih dimensinya, lalu tentukan kutub yang didukung jawaban "Setuju". Siswa menilai dengan skala Likert 1–5 (Sangat Tidak Sesuai – Sangat Sesuai) dan sistem menyusun kode 4 huruf (mis. INFP).</p>

        <div class="grid gap-3 sm:grid-cols-2">
            <div class="space-y-2">
                <x-input-label value="Dimensi kepribadian" />
                <x-form-select name="mbti_axis" x-model="axis" x-on:change="pole = ''" x-bind:required="isKepribadian">
                    @foreach($mbtiAxes as $axisKey => $letters)
                        <option value="{{ $axisKey }}" @selected($selectedAxis === $axisKey)>
                            {{ implode(' vs ', array_map(fn ($name, $letter) => "{$name} ({$letter})", $letters, array_keys($letters))) }}
                        </option>
                    @endforeach
                </x-form-select>
                <x-input-error :messages="$errors->get('mbti_axis')" />
            </div>

            <div class="space-y-2">
                <x-input-label value='Kutub yang didukung jawaban "Setuju"' />
                <x-form-select name="mbti_pole" x-model="pole" x-bind:required="isKepribadian">
                    <option value="">Pilih kutub</option>
                    <template x-for="(name, letter) in (axes[axis] || {})" :key="letter">
                        <option :value="letter" x-text="name + ' (' + letter + ')'"></option>
                    </template>
                </x-form-select>
                <x-input-error :messages="$errors->get('mbti_pole')" />
            </div>
        </div>

        <p class="rounded-xl bg-white/70 px-3 py-2 text-xs font-medium text-slate-600 dark:bg-slate-900/60 dark:text-slate-400">
            Skala jawaban terkunci: Sangat Tidak Sesuai (1) · Tidak Sesuai (2) · Netral (3) · Sesuai (4) · Sangat Sesuai (5) — sama seperti instrumen minat bakat.
        </p>
    </div>

    <div x-show="!isKepribadian" x-cloak class="space-y-3">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">Pilihan Jawaban dan Skor</p>
            <div class="flex flex-wrap gap-2">
                <button
                    type="button"
                    x-on:click="applyLikert()"
                    class="rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60"
                >
                    Pakai template Likert 1–5
                </button>
                <button
                    type="button"
                    x-on:click="addOption()"
                    x-bind:disabled="options.length >= 6"
                    class="rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white transition hover:bg-slate-700 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    Tambah opsi
                </button>
            </div>
        </div>

        <template x-for="(option, index) in options" :key="index">
            <div class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_110px_auto]">
                <input
                    type="text"
                    x-bind:name="`options[${index}][label]`"
                    x-model="option.label"
                    x-bind:required="!isKepribadian"
                    placeholder="Label jawaban"
                    class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm text-slate-900 dark:text-slate-100 shadow-xs focus:border-blue-400 focus:outline-hidden focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/50"
                >
                <input
                    type="number"
                    min="0"
                    max="100"
                    x-bind:name="`options[${index}][score]`"
                    x-model.number="option.score"
                    x-bind:required="!isKepribadian"
                    placeholder="Skor"
                    class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm text-slate-900 dark:text-slate-100 shadow-xs focus:border-blue-400 focus:outline-hidden focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/50"
                >
                <button
                    type="button"
                    x-on:click="removeOption(index)"
                    x-bind:disabled="options.length <= 2"
                    class="rounded-xl border border-red-200 px-3 py-2 text-xs font-semibold text-red-600 transition hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-red-900/50 dark:text-red-300 dark:hover:bg-red-950/30"
                >
                    Hapus
                </button>
            </div>
        </template>
        <p class="text-xs text-slate-500 dark:text-slate-400">Minimal 2 dan maksimal 6 pilihan jawaban. Default Talents Mapping: Sangat Tidak Suka (1) – Sangat Suka (5).</p>
    </div>

    <label class="flex items-center gap-3 rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm font-medium text-slate-700 dark:text-slate-300">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $question?->is_active ?? true)) class="rounded-sm border-slate-300 dark:border-slate-600 text-blue-600 focus:ring-blue-500">
        Aktif dan tampil untuk siswa
    </label>

    <x-primary-button>{{ $submit }}</x-primary-button>
</div>
