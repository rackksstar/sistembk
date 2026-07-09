@php
    $options = old('options', $question?->options ?? [
        ['label' => 'Sangat Tidak Sesuai', 'score' => 1],
        ['label' => 'Tidak Sesuai', 'score' => 2],
        ['label' => 'Sesuai', 'score' => 3],
        ['label' => 'Sangat Sesuai', 'score' => 4],
    ]);
@endphp

<div class="space-y-4">
    <div class="space-y-2">
        <x-input-label for="category_{{ $question?->id ?? 'new' }}" value="Jenis Instrumen" />
        <x-form-select id="category_{{ $question?->id ?? 'new' }}" name="category" required>
            <option value="">Pilih kategori</option>
            @foreach($categories as $value => $label)
                <option value="{{ $value }}" @selected(old('category', $question?->category) === $value)>{{ $label }}</option>
            @endforeach
        </x-form-select>
    </div>

    <div class="space-y-2">
        <x-input-label for="question_{{ $question?->id ?? 'new' }}" value="Soal" />
        <textarea id="question_{{ $question?->id ?? 'new' }}" name="question" rows="4" required class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm text-slate-900 dark:text-slate-100 shadow-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/50">{{ old('question', $question?->question) }}</textarea>
    </div>

    <div class="space-y-3">
        <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">Pilihan Jawaban dan Skor</p>
        @foreach($options as $index => $option)
            <div class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_110px]">
                <x-text-input name="options[{{ $index }}][label]" value="{{ $option['label'] ?? '' }}" required placeholder="Label jawaban" />
                <x-text-input name="options[{{ $index }}][score]" type="number" min="0" max="100" value="{{ $option['score'] ?? 0 }}" required placeholder="Skor" />
            </div>
        @endforeach
    </div>

    <label class="flex items-center gap-3 rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm font-medium text-slate-700 dark:text-slate-300">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $question?->is_active ?? true)) class="rounded border-slate-300 dark:border-slate-600 text-blue-600 focus:ring-blue-500">
        Aktif dan tampil untuk siswa
    </label>

    <x-primary-button>{{ $submit }}</x-primary-button>
</div>
