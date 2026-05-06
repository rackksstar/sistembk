<div class="space-y-2">
    <label class="block text-sm font-semibold text-slate-900" for="nama-{{ $sekolah?->id ?? 'create' }}">Nama Sekolah</label>
    <input id="nama-{{ $sekolah?->id ?? 'create' }}" name="nama" value="{{ old('nama', $sekolah?->nama) }}" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="SMA Negeri 1 ..." />
    <x-input-error :messages="$errors->get('nama')" class="text-sm text-red-600" />
</div>

<div class="grid gap-4 sm:grid-cols-2">
    <div class="space-y-2">
        <label class="block text-sm font-semibold text-slate-900" for="paket-{{ $sekolah?->id ?? 'create' }}">Paket Aktif</label>
        <input id="paket-{{ $sekolah?->id ?? 'create' }}" name="paket_aktif" value="{{ old('paket_aktif', $sekolah?->paket_aktif) }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="Basic / Pro (opsional)" />
        <x-input-error :messages="$errors->get('paket_aktif')" class="text-sm text-red-600" />
    </div>
    <div class="space-y-2">
        <label class="block text-sm font-semibold text-slate-900" for="aktivasi-{{ $sekolah?->id ?? 'create' }}">Tanggal Aktivasi</label>
        <input id="aktivasi-{{ $sekolah?->id ?? 'create' }}" name="tanggal_aktivasi" type="date" value="{{ old('tanggal_aktivasi', $sekolah?->tanggal_aktivasi?->format('Y-m-d')) }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100" />
        <x-input-error :messages="$errors->get('tanggal_aktivasi')" class="text-sm text-red-600" />
    </div>
</div>

<div class="space-y-2">
    <label class="block text-sm font-semibold text-slate-900" for="active-{{ $sekolah?->id ?? 'create' }}">Status</label>
    <select id="active-{{ $sekolah?->id ?? 'create' }}" name="is_active" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
        <option value="1" @selected((string) old('is_active', (int) ($sekolah?->is_active ?? true)) === '1')>Aktif</option>
        <option value="0" @selected((string) old('is_active', (int) ($sekolah?->is_active ?? true)) === '0')>Nonaktif</option>
    </select>
    <x-input-error :messages="$errors->get('is_active')" class="text-sm text-red-600" />
</div>

<button type="submit" class="w-full rounded-2xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-blue-500">
    {{ $submit }}
</button>

