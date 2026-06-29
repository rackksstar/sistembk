<div class="space-y-4">
    <div class="grid gap-4 md:grid-cols-3">
        <div>
            <x-input-label value="Bulan" />
            <x-text-input name="month" type="number" min="1" max="12" required value="{{ old('month', $journal?->month ?? now()->month) }}" class="mt-1 w-full" />
        </div>
        <div>
            <x-input-label value="Tahun" />
            <x-text-input name="year" type="number" min="2020" max="2100" required value="{{ old('year', $journal?->year ?? now()->year) }}" class="mt-1 w-full" />
        </div>
        <div>
            <x-input-label value="Judul" />
            <x-text-input name="title" required value="{{ old('title', $journal?->title) }}" class="mt-1 w-full" />
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
        <x-text-input name="individual_services" type="number" min="0" required value="{{ old('individual_services', $journal?->individual_services ?? 0) }}" placeholder="Jumlah individu" />
        <x-text-input name="group_services" type="number" min="0" required value="{{ old('group_services', $journal?->group_services ?? 0) }}" placeholder="Jumlah kelompok" />
        <x-text-input name="classical_services" type="number" min="0" required value="{{ old('classical_services', $journal?->classical_services ?? 0) }}" placeholder="Jumlah klasikal" />
    </div>

    <textarea name="summary" rows="5" required class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm" placeholder="Ringkasan kegiatan">{{ old('summary', $journal?->summary) }}</textarea>
    <textarea name="evaluation" rows="4" class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm" placeholder="Evaluasi">{{ old('evaluation', $journal?->evaluation) }}</textarea>
    <textarea name="follow_up" rows="4" class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm" placeholder="Tindak lanjut">{{ old('follow_up', $journal?->follow_up) }}</textarea>

    <x-primary-button>{{ $submit }}</x-primary-button>
</div>
