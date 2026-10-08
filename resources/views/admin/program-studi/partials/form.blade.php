@php
    $suffix = $programStudi?->id ?? 'create';
    $selectedCategories = old('categories');
    if ($selectedCategories === null && $programStudi) {
        $selectedCategories = $programStudi->interestCategories
            ->mapWithKeys(fn ($cat) => [$cat->id => ['enabled' => '1', 'relevansi' => $cat->pivot->relevansi]])
            ->all();
    }
    $selectedCategories = $selectedCategories ?? [];
@endphp

<div class="space-y-2">
    <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100" for="institusi-{{ $suffix }}">Institusi</label>
    <input id="institusi-{{ $suffix }}" name="institusi" value="{{ old('institusi', $programStudi?->institusi ?? 'Politeknik Caltex Riau') }}" required maxlength="150" class="ui-input js-select2" />
    <x-input-error :messages="$errors->get('institusi')" class="text-sm text-red-600" />
</div>

<div class="space-y-2">
    <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100" for="nama-{{ $suffix }}">Nama Program Studi</label>
    <input id="nama-{{ $suffix }}" name="nama" value="{{ old('nama', $programStudi?->nama) }}" required maxlength="150" class="ui-input js-select2" placeholder="Teknik Informatika" />
    <x-input-error :messages="$errors->get('nama')" class="text-sm text-red-600" />
</div>

<div class="grid gap-4 sm:grid-cols-2">
    <div class="space-y-2">
        <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100" for="jenjang-{{ $suffix }}">Jenjang</label>
        <select id="jenjang-{{ $suffix }}" name="jenjang_pendidikan" required class="ui-input js-select2">
            <option value="">Pilih jenjang</option>
            @foreach(['D3', 'D4'] as $jenjang)
                <option value="{{ $jenjang }}" @selected(old('jenjang_pendidikan', $programStudi?->jenjang_pendidikan) === $jenjang)>{{ $jenjang }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('jenjang_pendidikan')" class="text-sm text-red-600" />
    </div>
    <div class="space-y-2">
        <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100" for="jurusan-{{ $suffix }}">Jurusan</label>
        <input id="jurusan-{{ $suffix }}" name="jurusan" value="{{ old('jurusan', $programStudi?->jurusan) }}" maxlength="150" class="ui-input js-select2" placeholder="Teknik" />
        <x-input-error :messages="$errors->get('jurusan')" class="text-sm text-red-600" />
    </div>
</div>

<div class="space-y-2">
    <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100" for="deskripsi-{{ $suffix }}">Deskripsi</label>
    <textarea id="deskripsi-{{ $suffix }}" name="deskripsi" rows="3" class="ui-input js-select2">{{ old('deskripsi', $programStudi?->deskripsi) }}</textarea>
    <x-input-error :messages="$errors->get('deskripsi')" class="text-sm text-red-600" />
</div>

<div class="space-y-2">
    <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100" for="prospek-{{ $suffix }}">Prospek Karier</label>
    <textarea id="prospek-{{ $suffix }}" name="prospek_karier" rows="2" class="ui-input js-select2">{{ old('prospek_karier', $programStudi?->prospek_karier) }}</textarea>
    <x-input-error :messages="$errors->get('prospek_karier')" class="text-sm text-red-600" />
</div>

<div class="space-y-2">
    <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100" for="website-{{ $suffix }}">Website</label>
    <input id="website-{{ $suffix }}" type="url" name="website_url" value="{{ old('website_url', $programStudi?->website_url) }}" class="ui-input js-select2" placeholder="https://pmb.pcr.ac.id/..." />
    <x-input-error :messages="$errors->get('website_url')" class="text-sm text-red-600" />
</div>

<div class="grid gap-4 sm:grid-cols-2">
    <div class="space-y-2">
        <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100" for="verified-{{ $suffix }}">Verifikasi</label>
        <select id="verified-{{ $suffix }}" name="is_verified" required class="ui-input js-select2">
            <option value="0" @selected((string) old('is_verified', (int) ($programStudi?->is_verified ?? false)) === '0')>Belum terverifikasi</option>
            <option value="1" @selected((string) old('is_verified', (int) ($programStudi?->is_verified ?? false)) === '1')>Terverifikasi</option>
        </select>
        <x-input-error :messages="$errors->get('is_verified')" class="text-sm text-red-600" />
    </div>
    <div class="space-y-2">
        <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100" for="active-{{ $suffix }}">Status</label>
        <select id="active-{{ $suffix }}" name="is_active" required class="ui-input js-select2">
            <option value="1" @selected((string) old('is_active', (int) ($programStudi?->is_active ?? true)) === '1')>Aktif</option>
            <option value="0" @selected((string) old('is_active', (int) ($programStudi?->is_active ?? true)) === '0')>Nonaktif</option>
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
                    <input type="checkbox" name="categories[{{ $cat->id }}][enabled]" value="1" @checked($enabled) class="rounded-sm border-slate-300 text-blue-600 focus:ring-blue-500" />
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
