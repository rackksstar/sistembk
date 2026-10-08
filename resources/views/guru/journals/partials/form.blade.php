<div class="space-y-4">
    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <x-input-label value="Hari/Tanggal" />
            <input type="date" name="entry_date" required value="{{ old('entry_date', $journal?->entryDate()?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" class="mt-1 w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm text-slate-900 dark:text-slate-100" />
        </div>
        <div>
            <x-input-label value="Judul" />
            <x-text-input name="title" required value="{{ old('title', $journal?->title) }}" class="mt-1 w-full" />
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
        <div>
            <x-input-label value="Jenis Kegiatan" />
            <select name="service_type" required class="mt-1 w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm text-slate-900 dark:text-slate-100">
                @foreach(\App\Models\MonthlyJournal::SERVICE_TYPES as $value => $label)
                    <option value="{{ $value }}" @selected(old('service_type', $journal?->service_type) === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <x-input-label value="Kategori Masalah" />
            <select name="case_category" required class="mt-1 w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm text-slate-900 dark:text-slate-100">
                @foreach(\App\Models\ConsultationRequest::CASE_CATEGORIES as $value => $label)
                    <option value="{{ $value }}" @selected(old('case_category', $journal?->case_category) === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <x-input-label value="Sasaran Kegiatan" />
            <select name="target_type" required class="mt-1 w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm text-slate-900 dark:text-slate-100">
                @foreach(\App\Models\MonthlyJournal::TARGET_TYPES as $value => $label)
                    <option value="{{ $value }}" @selected(old('target_type', $journal?->target_type) === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div>
        <x-input-label value="Nama Sasaran (Siswa/Kelas/Pihak Terkait)" />
        <x-text-input name="target_name" required value="{{ old('target_name', $journal?->target_name) }}" placeholder="Contoh: Ahmad Fauzi, XII IPA 1, atau Wali Kelas" class="mt-1 w-full" />
    </div>

    <div class="grid gap-4 md:grid-cols-3">
        <x-text-input name="individual_services" type="number" min="0" required value="{{ old('individual_services', $journal?->individual_services ?? 0) }}" placeholder="Jumlah individu" />
        <x-text-input name="group_services" type="number" min="0" required value="{{ old('group_services', $journal?->group_services ?? 0) }}" placeholder="Jumlah kelompok" />
        <x-text-input name="classical_services" type="number" min="0" required value="{{ old('classical_services', $journal?->classical_services ?? 0) }}" placeholder="Jumlah klasikal" />
    </div>

    <textarea name="summary" rows="5" required class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm" placeholder="Deskripsi/Uraian Kegiatan">{{ old('summary', $journal?->summary) }}</textarea>

    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-4">
        <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">Hasil / Tindak Lanjut</p>
        <div class="mt-3 grid gap-4 md:grid-cols-2">
            <textarea name="outcome" rows="4" class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm" placeholder="Hasil yang dicapai">{{ old('outcome', $journal?->outcome) }}</textarea>
            <textarea name="follow_up" rows="4" class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm" placeholder="Tindak lanjut">{{ old('follow_up', $journal?->follow_up) }}</textarea>
        </div>
        <textarea name="evaluation" rows="3" class="mt-4 w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm" placeholder="Evaluasi">{{ old('evaluation', $journal?->evaluation) }}</textarea>
    </div>

    <x-primary-button>{{ $submit }}</x-primary-button>
</div>
