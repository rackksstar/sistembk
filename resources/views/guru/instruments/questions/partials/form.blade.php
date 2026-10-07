@php
    $formKey = $question?->id ?? 'new';
    $defaultOptions = [
        ['label' => 'Sangat Tidak Sesuai', 'score' => 1],
        ['label' => 'Tidak Sesuai', 'score' => 2],
        ['label' => 'Sesuai', 'score' => 3],
        ['label' => 'Sangat Sesuai', 'score' => 4],
    ];
    $initialOptions = collect(old('options', $question?->options ?? $defaultOptions))
        ->map(fn ($option) => [
            'label' => $option['label'] ?? '',
            'score' => (int) ($option['score'] ?? 0),
        ])
        ->values()
        ->all();
@endphp

<div
    class="space-y-4"
    x-data="{
        category: @js(old('category', $question?->category ?? '')),
        interestCategoryId: @js(old('interest_category_id', $question?->interest_category_id)),
        jenjangTarget: @js(old('jenjang_target', $question?->jenjang_target ?? 'semua')),
        bobot: @js((int) old('bobot', $question?->bobot ?? 1)),
        options: @js($initialOptions),
        likertTemplate: [
            { label: 'Sangat tidak tertarik', score: 0 },
            { label: 'Tidak tertarik', score: 1 },
            { label: 'Netral', score: 2 },
            { label: 'Tertarik', score: 3 },
            { label: 'Sangat tertarik', score: 4 },
        ],
        get isMinatBakat() {
            return this.category === 'minat_bakat';
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
            this.$watch('category', refresh);
            this.$watch('isMinatBakat', refresh);
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
    </div>

    <div class="space-y-2">
        <x-input-label for="question_{{ $formKey }}" value="Soal" />
        <textarea id="question_{{ $formKey }}" name="question" rows="4" required class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm text-slate-900 dark:text-slate-100 shadow-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/50">{{ old('question', $question?->question) }}</textarea>
    </div>

    <div x-show="isMinatBakat" x-cloak class="space-y-4 rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/40 p-4">
        <div class="space-y-2">
            <x-input-label for="interest_category_id_{{ $formKey }}" value="Kategori Minat" />
            <x-form-select
                id="interest_category_id_{{ $formKey }}"
                name="interest_category_id"
                x-model="interestCategoryId"
                x-bind:required="isMinatBakat"
            >
                <option value="">Pilih kategori minat</option>
                @foreach($interestCategories as $interestCategory)
                    <option value="{{ $interestCategory->id }}">{{ $interestCategory->kode }} — {{ $interestCategory->nama }}</option>
                @endforeach
            </x-form-select>
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

    <div class="space-y-3">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">Pilihan Jawaban dan Skor</p>
            <div class="flex flex-wrap gap-2">
                <button
                    type="button"
                    x-on:click="applyLikert()"
                    class="rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60"
                >
                    Pakai template Likert 0–4
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
                    required
                    placeholder="Label jawaban"
                    class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm text-slate-900 dark:text-slate-100 shadow-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/50"
                >
                <input
                    type="number"
                    min="0"
                    max="100"
                    x-bind:name="`options[${index}][score]`"
                    x-model.number="option.score"
                    required
                    placeholder="Skor"
                    class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm text-slate-900 dark:text-slate-100 shadow-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/50"
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
        <p class="text-xs text-slate-500 dark:text-slate-400">Minimal 2 dan maksimal 6 pilihan jawaban.</p>
    </div>

    <label class="flex items-center gap-3 rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm font-medium text-slate-700 dark:text-slate-300">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $question?->is_active ?? true)) class="rounded border-slate-300 dark:border-slate-600 text-blue-600 focus:ring-blue-500">
        Aktif dan tampil untuk siswa
    </label>

    <x-primary-button>{{ $submit }}</x-primary-button>
</div>
