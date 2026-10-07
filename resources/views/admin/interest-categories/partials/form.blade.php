@php
    $suffix = $category?->id ?? 'create';
@endphp

<div class="grid gap-4 sm:grid-cols-2">
    <div class="space-y-2">
        <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100" for="kode-{{ $suffix }}">Kode</label>
        <input id="kode-{{ $suffix }}" name="kode" value="{{ old('kode', $category?->kode) }}" required maxlength="5" class="js-select2 w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm uppercase focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/50" placeholder="R" />
        <x-input-error :messages="$errors->get('kode')" class="text-sm text-red-600" />
    </div>
    <div class="space-y-2">
        <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100" for="urutan-{{ $suffix }}">Urutan</label>
        <input id="urutan-{{ $suffix }}" type="number" min="0" name="urutan" value="{{ old('urutan', $category?->urutan ?? 0) }}" class="ui-input js-select2" />
        <x-input-error :messages="$errors->get('urutan')" class="text-sm text-red-600" />
    </div>
</div>

<div class="space-y-2">
    <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100" for="nama-{{ $suffix }}">Nama</label>
    <input id="nama-{{ $suffix }}" name="nama" value="{{ old('nama', $category?->nama) }}" required maxlength="120" class="ui-input js-select2" placeholder="Teknik" />
    <x-input-error :messages="$errors->get('nama')" class="text-sm text-red-600" />
</div>

<div class="space-y-2">
    <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100" for="deskripsi-{{ $suffix }}">Deskripsi</label>
    <textarea id="deskripsi-{{ $suffix }}" name="deskripsi" rows="3" class="ui-input js-select2" placeholder="Ringkasan minat kategori ini...">{{ old('deskripsi', $category?->deskripsi) }}</textarea>
    <x-input-error :messages="$errors->get('deskripsi')" class="text-sm text-red-600" />
</div>

<div class="grid gap-4 sm:grid-cols-2">
    <div class="space-y-2">
        <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100" for="warna-{{ $suffix }}">Warna</label>
        <input id="warna-{{ $suffix }}" name="warna" value="{{ old('warna', $category?->warna) }}" maxlength="20" class="ui-input js-select2" placeholder="#f59e0b" />
        <x-input-error :messages="$errors->get('warna')" class="text-sm text-red-600" />
    </div>
    <div class="space-y-2">
        <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100" for="active-{{ $suffix }}">Status</label>
        <select id="active-{{ $suffix }}" name="is_active" required class="ui-input js-select2">
            <option value="1" @selected((string) old('is_active', (int) ($category?->is_active ?? true)) === '1')>Aktif</option>
            <option value="0" @selected((string) old('is_active', (int) ($category?->is_active ?? true)) === '0')>Nonaktif</option>
        </select>
        <x-input-error :messages="$errors->get('is_active')" class="text-sm text-red-600" />
    </div>
</div>

<button type="submit" class="w-full rounded-2xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-blue-500">
    {{ $submit }}
</button>
