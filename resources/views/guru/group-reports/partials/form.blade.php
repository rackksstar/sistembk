@php($formId = $report?->id ?? 'new')

<div class="grid gap-4">
    <div class="grid gap-4 md:grid-cols-2">
        <div class="space-y-2 md:col-span-2">
            <x-input-label for="rpl_id_{{ $formId }}" value="RPL Konseling Kelompok" />
            <x-form-select id="rpl_id_{{ $formId }}" name="rpl_id" required>
                <option value="">Pilih RPL kelompok</option>
                @foreach($groupRpls as $rpl)
                    <option value="{{ $rpl->id }}" @selected(old('rpl_id', $report?->rpl_id) == $rpl->id)>
                        {{ $rpl->title }} - {{ $rpl->classRoom?->name ?? 'Tanpa kelas' }} - {{ $rpl->groupStudents->count() }} siswa
                    </option>
                @endforeach
            </x-form-select>
        </div>

        <div class="space-y-2">
            <x-input-label for="title_{{ $formId }}" value="Judul Laporan" />
            <x-text-input id="title_{{ $formId }}" name="title" value="{{ old('title', $report?->title) }}" required />
        </div>
        <div class="space-y-2">
            <x-input-label for="case_category_{{ $formId }}" value="Kategori Kasus" />
            <x-form-select id="case_category_{{ $formId }}" name="case_category" required>
                <option value="">Pilih kategori</option>
                @foreach($caseCategories as $value => $label)
                    <option value="{{ $value }}" @selected(old('case_category', $report?->case_category) === $value)>{{ $label }}</option>
                @endforeach
            </x-form-select>
        </div>
        <div class="space-y-2">
            <x-input-label for="service_date_{{ $formId }}" value="Tanggal Layanan" />
            <x-text-input id="service_date_{{ $formId }}" name="service_date" type="date" value="{{ old('service_date', $report?->service_date?->format('Y-m-d')) }}" required />
        </div>
        <div class="space-y-2">
            <x-input-label for="duration_minutes_{{ $formId }}" value="Durasi Menit" />
            <x-text-input id="duration_minutes_{{ $formId }}" name="duration_minutes" type="number" min="1" max="600" value="{{ old('duration_minutes', $report?->duration_minutes) }}" />
        </div>
        <div class="space-y-2 md:col-span-2">
            <x-input-label for="location_{{ $formId }}" value="Tempat" />
            <x-text-input id="location_{{ $formId }}" name="location" value="{{ old('location', $report?->location) }}" placeholder="Contoh: Ruang BK" />
        </div>
    </div>

    @foreach(['result' => 'Hasil Konseling Kelompok', 'evaluation' => 'Evaluasi', 'follow_up' => 'Tindak Lanjut'] as $field => $label)
        <div class="space-y-2">
            <x-input-label for="{{ $field }}_{{ $formId }}" :value="$label" />
            <textarea id="{{ $field }}_{{ $formId }}" name="{{ $field }}" rows="4" @required($field !== 'follow_up') class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">{{ old($field, $report?->{$field}) }}</textarea>
        </div>
    @endforeach

    <x-primary-button>{{ $submit }}</x-primary-button>
</div>
