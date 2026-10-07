@php
    $suffix = $careerField?->id ?? 'create';
    $selectedCategories = old('categories');
    if ($selectedCategories === null && $careerField) {
        $selectedCategories = $careerField->interestCategories
            ->mapWithKeys(fn ($cat) => [$cat->id => ['enabled' => '1', 'relevansi' => $cat->pivot->relevansi]])
            ->all();
    }
    $selectedCategories = $selectedCategories ?? [];

    $jobsText = old('contoh_pekerjaan_text');
    if ($jobsText === null && $careerField) {
        $jobsText = implode("\n", $careerField->contoh_pekerjaan ?? []);
    }
@endphp

<div class="space-y-2">
    <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100" for="nama-{{ $suffix }}">Nama Bidang Karier</label>
    <input id="nama-{{ $suffix }}" name="nama" value="{{ old('nama', $careerField?->nama) }}" required maxlength="150" class="ui-input js-select2" placeholder="Teknologi Informasi" />
    <x-input-error :messages="$errors->get('nama')" class="text-sm text-red-600" />
</div>

<div class="space-y-2">
    <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100" for="deskripsi-{{ $suffix }}">Deskripsi</label>
    <textarea id="deskripsi-{{ $suffix }}" name="deskripsi" rows="3" class="ui-input js-select2">{{ old('deskripsi', $careerField?->deskripsi) }}</textarea>
    <x-input-error :messages="$errors->get('deskripsi')" class="text-sm text-red-600" />
</div>

<div class="space-y-2">
    <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100" for="jobs-{{ $suffix }}">Contoh Pekerjaan</label>
    <textarea id="jobs-{{ $suffix }}" name="contoh_pekerjaan_text" rows="4" class="ui-input js-select2" placeholder="Satu pekerjaan per baris">{{ $jobsText }}</textarea>
    <p class="text-xs text-slate-500 dark:text-slate-400">Tulis satu contoh pekerjaan per baris.</p>
    <x-input-error :messages="$errors->get('contoh_pekerjaan_text')" class="text-sm text-red-600" />
</div>

<div class="grid gap-4 sm:grid-cols-2">
    <div class="space-y-2">
        <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100" for="job-zone-{{ $suffix }}">Job Zone</label>
        <select id="job-zone-{{ $suffix }}" name="job_zone" class="ui-input js-select2">
            <option value="">Belum ditentukan</option>
            @for($i = 1; $i <= 5; $i++)
                <option value="{{ $i }}" @selected((string) old('job_zone', $careerField?->job_zone) === (string) $i)>Job Zone {{ $i }}</option>
            @endfor
        </select>
        <x-input-error :messages="$errors->get('job_zone')" class="text-sm text-red-600" />
    </div>
    <div class="space-y-2">
        <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100" for="active-{{ $suffix }}">Status</label>
        <select id="active-{{ $suffix }}" name="is_active" required class="ui-input js-select2">
            <option value="1" @selected((string) old('is_active', (int) ($careerField?->is_active ?? true)) === '1')>Aktif</option>
            <option value="0" @selected((string) old('is_active', (int) ($careerField?->is_active ?? true)) === '0')>Nonaktif</option>
        </select>
        <x-input-error :messages="$errors->get('is_active')" class="text-sm text-red-600" />
    </div>
</div>

<div class="space-y-3">
    <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">Kategori Minat & Relevansi</p>
    <div class="space-y-2 rounded-2xl border border-slate-200 dark:border-slate-700 p-4">
        @forelse($interestCategories as $cat)
            @php
                $enabled = (string) data_get($selectedCategories, "{$cat->id}.enabled", '0') === '1';
                $relevansi = (int) data_get($selectedCategories, "{$cat->id}.relevansi", 2);
            @endphp
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                    <input type="checkbox" name="categories[{{ $cat->id }}][enabled]" value="1" @checked($enabled) class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                    <span class="font-semibold">{{ $cat->kode }}</span>
                    <span>{{ $cat->nama }}</span>
                </label>
                <select name="categories[{{ $cat->id }}][relevansi]" class="ui-input js-select2 w-full sm:w-36">
                    @for($i = 1; $i <= 3; $i++)
                        <option value="{{ $i }}" @selected($relevansi === $i)>Relevansi {{ $i }}</option>
                    @endfor
                </select>
            </div>
        @empty
            <p class="text-sm text-slate-500 dark:text-slate-400">Belum ada kategori minat. Tambahkan di menu Kategori Minat terlebih dahulu.</p>
        @endforelse
    </div>
    <x-input-error :messages="$errors->get('categories')" class="text-sm text-red-600" />
</div>

<button type="submit" class="w-full rounded-2xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-blue-500">
    {{ $submit }}
</button>
