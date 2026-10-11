@php($formId = $report?->id ?? 'new')

<div class="grid gap-4">
    @if(!$report)
        <div class="space-y-2">
            <x-input-label for="consultation_id_{{ $formId }}" value="Sesi Konseling Individu" />
            <x-form-select id="consultation_id_{{ $formId }}" name="consultation_id" required>
                <option value="">Pilih sesi konseling individu</option>
                @foreach($availableConsultations as $session)
                    <option value="{{ $session->id }}" @selected((string) old('consultation_id') === (string) $session->id)>
                        {{ $session->student?->name ?? 'Siswa tidak tersedia' }} - {{ $session->subject }} - {{ $session->consultation_date?->format('d M Y') ?? 'Belum dijadwalkan' }}
                    </option>
                @endforeach
            </x-form-select>
            <x-input-error :messages="$errors->get('consultation_id')" />
        </div>
    @else
        <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/60 p-4 text-sm text-slate-600 dark:text-slate-400">
            <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $report->student?->name }} · {{ $report->subject }}</p>
            <p class="mt-1">{{ $report->consultation_date?->format('d M Y') ?? 'Tanggal belum ditentukan' }} · {{ $report->caseCategoryLabel() }}</p>
        </div>
    @endif

    <div class="grid gap-4 md:grid-cols-2">
        <div class="space-y-2 md:col-span-2">
            <x-input-label for="report_title_{{ $formId }}" value="Judul Laporan" />
            <x-text-input id="report_title_{{ $formId }}" name="report_title" value="{{ old('report_title', $report?->report_title ?? $report?->subject) }}" required />
            <x-input-error :messages="$errors->get('report_title')" />
        </div>
        <div class="space-y-2">
            <x-input-label for="meeting_number_{{ $formId }}" value="Pertemuan ke-" />
            <x-text-input id="meeting_number_{{ $formId }}" name="meeting_number" type="number" min="1" max="100" value="{{ old('meeting_number', $report?->meeting_number) }}" />
            <x-input-error :messages="$errors->get('meeting_number')" />
        </div>
        <div class="space-y-2">
            <x-input-label for="duration_minutes_{{ $formId }}" value="Durasi Menit" />
            <x-text-input id="duration_minutes_{{ $formId }}" name="duration_minutes" type="number" min="1" max="600" value="{{ old('duration_minutes', $report?->duration_minutes) }}" />
            <x-input-error :messages="$errors->get('duration_minutes')" />
        </div>
        <div class="space-y-2 md:col-span-2">
            <x-input-label for="location_{{ $formId }}" value="Tempat" />
            <x-text-input id="location_{{ $formId }}" name="location" value="{{ old('location', $report?->location) }}" placeholder="Contoh: Ruang BK" />
            <x-input-error :messages="$errors->get('location')" />
        </div>
        <div class="space-y-2 md:col-span-2">
            <x-input-label for="approach_technique_{{ $formId }}" value="Pendekatan dan Teknik" />
            <textarea id="approach_technique_{{ $formId }}" name="approach_technique" rows="3" class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm">{{ old('approach_technique', $report?->approach_technique) }}</textarea>
            <x-input-error :messages="$errors->get('approach_technique')" />
        </div>
        <div class="space-y-2">
            <x-input-label for="semester_{{ $formId }}" value="Semester" />
            <x-form-select id="semester_{{ $formId }}" name="semester">
                <option value="">Pilih semester</option>
                <option value="1" @selected((string) old('semester', $report?->semester) === '1')>Semester 1</option>
                <option value="2" @selected((string) old('semester', $report?->semester) === '2')>Semester 2</option>
            </x-form-select>
            <x-input-error :messages="$errors->get('semester')" />
        </div>
        <div class="space-y-2">
            <x-input-label for="year_{{ $formId }}" value="Tahun Pelajaran" />
            <x-text-input id="year_{{ $formId }}" name="year" type="number" min="2020" max="2100" value="{{ old('year', $report?->year ?? now()->year) }}" />
            <x-input-error :messages="$errors->get('year')" />
        </div>
    </div>

    @foreach(['result' => 'Hasil Konseling Individu', 'evaluation' => 'Evaluasi', 'follow_up' => 'Tindak Lanjut'] as $field => $label)
        <div class="space-y-2">
            <x-input-label for="{{ $field }}_{{ $formId }}" :value="$label" />
            <textarea id="{{ $field }}_{{ $formId }}" name="{{ $field }}" rows="4" @required($field !== 'follow_up') class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm text-slate-900 dark:text-slate-100 shadow-xs focus:border-blue-400 focus:outline-hidden focus:ring-2 focus:ring-blue-100">{{ old($field, $report?->{$field}) }}</textarea>
            <x-input-error :messages="$errors->get($field)" />
        </div>
    @endforeach

    <x-primary-button>{{ $submit }}</x-primary-button>
</div>
