@php
    $formId = $rpl?->id ?? 'new';
    $selectedType = old('type', $rpl?->type ?? \App\Models\Rpl::TYPE_INDIVIDU);
    $selectedClassId = (string) old('class_id', $rpl?->class_id);
    $selectedStudentId = (string) old('student_id', $rpl?->student_id);
    $selectedGroupIds = collect(old('group_student_ids', $rpl?->groupStudents?->pluck('id')->all() ?? []))->map(fn ($id) => (string) $id)->all();
@endphp

<div
    class="grid gap-4"
    x-data="{
        jenis: @js($selectedType),
        kelas: @js($selectedClassId),
        cariIndividu: '',
        cariKelompok: '',
        cocokSiswa(nama, kelasId, pencarian) {
            return this.kelas === String(kelasId) && nama.toLowerCase().includes(pencarian.toLowerCase());
        }
    }"
>
    <div class="grid gap-4 md:grid-cols-2">
        <div class="space-y-2">
            <x-input-label for="title_{{ $formId }}" value="Judul RPL" />
            <x-text-input id="title_{{ $formId }}" name="title" value="{{ old('title', $rpl?->title) }}" required />
        </div>
        <div class="space-y-2">
            <x-input-label for="type_{{ $formId }}" value="Jenis RPL" />
            <x-form-select id="type_{{ $formId }}" name="type" x-model="jenis" required>
                @foreach($types as $value => $label)
                    <option value="{{ $value }}" @selected($selectedType === $value)>{{ $label }}</option>
                @endforeach
            </x-form-select>
        </div>
        <div class="space-y-2">
            <x-input-label for="class_id_{{ $formId }}" value="Kelas" />
            <x-form-select id="class_id_{{ $formId }}" name="class_id" x-model="kelas" required>
                <option value="">Pilih kelas dulu</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}" @selected($selectedClassId === (string) $class->id)>{{ $class->name }}</option>
                @endforeach
            </x-form-select>
        </div>
        <div class="space-y-2">
            <x-input-label for="status_{{ $formId }}" value="Status RPL" />
            <x-form-select id="status_{{ $formId }}" name="status" required>
                @foreach($statuses as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $rpl?->status ?? \App\Models\Rpl::STATUS_AKTIF) === $value)>{{ $label }}</option>
                @endforeach
            </x-form-select>
        </div>
        <div class="space-y-2">
            <x-input-label for="semester_{{ $formId }}" value="Semester" />
            <x-form-select id="semester_{{ $formId }}" name="semester" required>
                <option value="1" @selected((string) old('semester', $rpl?->semester ?? 1) === '1')>Semester 1</option>
                <option value="2" @selected((string) old('semester', $rpl?->semester ?? 1) === '2')>Semester 2</option>
            </x-form-select>
        </div>
        <div class="space-y-2">
            <x-input-label for="year_{{ $formId }}" value="Tahun" />
            <x-text-input id="year_{{ $formId }}" name="year" type="number" min="2020" max="2100" value="{{ old('year', $rpl?->year ?? now()->year) }}" required />
        </div>
        <div class="space-y-2">
            <x-input-label for="service_date_{{ $formId }}" value="Tanggal Layanan" />
            <x-text-input id="service_date_{{ $formId }}" name="service_date" type="date" value="{{ old('service_date', $rpl?->service_date?->format('Y-m-d')) }}" />
        </div>
        <div class="space-y-2">
            <x-input-label for="target_{{ $formId }}" value="Sasaran" />
            <x-text-input id="target_{{ $formId }}" name="target" value="{{ old('target', $rpl?->target) }}" placeholder="Boleh dikosongkan, sistem isi otomatis" />
        </div>
        <div class="space-y-2">
            <x-input-label for="meeting_number_{{ $formId }}" value="Pertemuan ke-" />
            <x-text-input id="meeting_number_{{ $formId }}" name="meeting_number" type="number" min="1" max="100" value="{{ old('meeting_number', $rpl?->meeting_number) }}" placeholder="mis. 1" />
        </div>
        <div class="space-y-2">
            <x-input-label for="duration_minutes_{{ $formId }}" value="Durasi (menit)" />
            <x-text-input id="duration_minutes_{{ $formId }}" name="duration_minutes" type="number" min="1" max="600" value="{{ old('duration_minutes', $rpl?->duration_minutes) }}" placeholder="mis. 45" />
        </div>
        <div class="space-y-2">
            <x-input-label for="location_{{ $formId }}" value="Tempat Pelaksanaan" />
            <x-text-input id="location_{{ $formId }}" name="location" value="{{ old('location', $rpl?->location) }}" placeholder="mis. Ruang BK" />
        </div>
    </div>

    <div class="space-y-2">
        <x-input-label for="topik_permasalahan_{{ $formId }}" value="Topik Permasalahan" />
        <x-text-input id="topik_permasalahan_{{ $formId }}" name="topik_permasalahan" value="{{ old('topik_permasalahan', $rpl?->topik_permasalahan) }}" placeholder="Wajib diisi untuk RPL kelompok" />
        <p class="text-xs text-slate-500 dark:text-slate-400">Pertemuan ke-, durasi, tempat, dan topik wajib diisi khusus untuk RPL kelompok.</p>
    </div>

    <div class="space-y-2">
        <x-input-label for="media_{{ $formId }}" value="Media yang Diperlukan" />
        <textarea id="media_{{ $formId }}" name="media" rows="3" class="mt-1 w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm text-slate-900 dark:text-slate-100 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-500/40" placeholder="mis. LCD, lembar kerja siswa, papan tulis">{{ old('media', $rpl?->media) }}</textarea>
    </div>

    <div x-show="jenis === 'individu'" class="rounded-2xl border border-slate-200 dark:border-slate-700 p-4">
        <x-input-label value="Siswa RPL Individu" />
        <input type="search" x-model="cariIndividu" placeholder="Cari nama siswa" class="mt-2 w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm text-slate-900 dark:text-slate-100 focus:border-blue-400 focus:outline-hidden focus:ring-2 focus:ring-blue-100">
        <div class="mt-3 max-h-52 space-y-2 overflow-y-auto">
            @foreach($students as $student)
                <label
                    x-show="cocokSiswa(@js($student->name), @js($student->class_id), cariIndividu)"
                    class="flex items-center gap-3 rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-800 dark:border-slate-700 dark:text-slate-200"
                >
                    <input type="radio" name="student_id" value="{{ $student->id }}" @checked($selectedStudentId === (string) $student->id) class="border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-600">
                    <span>{{ $student->name }}</span>
                </label>
            @endforeach
        </div>
        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Pilih kelas dulu, lalu ketik minimal beberapa huruf nama siswa.</p>
    </div>

    <div x-show="jenis === 'kelompok'" class="rounded-2xl border border-slate-200 dark:border-slate-700 p-4">
        <x-input-label value="Anggota RPL Kelompok" />
        <input type="search" x-model="cariKelompok" placeholder="Cari nama siswa" class="mt-2 w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm text-slate-900 dark:text-slate-100 focus:border-blue-400 focus:outline-hidden focus:ring-2 focus:ring-blue-100">
        <div class="mt-3 max-h-56 space-y-2 overflow-y-auto">
            @foreach($students as $student)
                <label
                    x-show="cocokSiswa(@js($student->name), @js($student->class_id), cariKelompok)"
                    class="flex items-center gap-3 rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-800 dark:border-slate-700 dark:text-slate-200"
                >
                    <input type="checkbox" name="group_student_ids[]" value="{{ $student->id }}" @checked(in_array((string) $student->id, $selectedGroupIds, true)) class="rounded-sm border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-600">
                    <span>{{ $student->name }}</span>
                </label>
            @endforeach
        </div>
        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Pilih minimal 2 siswa dari kelas yang sama.</p>
    </div>

    @foreach(['tujuan' => 'Tujuan', 'materi' => 'Materi', 'metode' => 'Metode', 'evaluasi' => 'Evaluasi'] as $field => $label)
        <div class="space-y-2">
            <x-input-label for="{{ $field }}_{{ $formId }}" :value="$label" />
            <textarea id="{{ $field }}_{{ $formId }}" name="{{ $field }}" rows="4" required class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm text-slate-900 dark:text-slate-100 shadow-xs focus:border-blue-400 focus:outline-hidden focus:ring-2 focus:ring-blue-100">{{ old($field, $rpl?->{$field}) }}</textarea>
        </div>
    @endforeach

    <x-primary-button>{{ $submit }}</x-primary-button>
</div>
