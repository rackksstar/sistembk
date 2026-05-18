<div class="space-y-2">
    <label class="block text-sm font-semibold text-slate-900" for="sekolah-{{ $kelas?->id ?? 'create' }}">Sekolah</label>
    <select id="sekolah-{{ $kelas?->id ?? 'create' }}" name="sekolah_id" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
        <option value="">Pilih sekolah</option>
        @foreach($sekolahs as $s)
            <option value="{{ $s->id }}" @selected((string) old('sekolah_id', $kelas?->sekolah_id) === (string) $s->id)>{{ $s->nama }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('sekolah_id')" class="text-sm text-red-600" />
</div>

<div class="space-y-2">
    <label class="block text-sm font-semibold text-slate-900" for="nama-{{ $kelas?->id ?? 'create' }}">Nama Kelas</label>
    <input id="nama-{{ $kelas?->id ?? 'create' }}" name="nama" value="{{ old('nama', $kelas?->nama) }}" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="XII IPA 1" />
    <x-input-error :messages="$errors->get('nama')" class="text-sm text-red-600" />
</div>

<div class="grid gap-4 sm:grid-cols-2">
    <div class="space-y-2">
        <label class="block text-sm font-semibold text-slate-900" for="jenjang-{{ $kelas?->id ?? 'create' }}">Jenjang</label>
        <input id="jenjang-{{ $kelas?->id ?? 'create' }}" name="jenjang" value="{{ old('jenjang', $kelas?->jenjang) }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="SMA / SMK / SMP (opsional)" />
        <x-input-error :messages="$errors->get('jenjang')" class="text-sm text-red-600" />
    </div>
    <div class="space-y-2">
        <label class="block text-sm font-semibold text-slate-900" for="tingkatan-{{ $kelas?->id ?? 'create' }}">Tingkatan</label>
        <input id="tingkatan-{{ $kelas?->id ?? 'create' }}" name="tingkatan" value="{{ old('tingkatan', $kelas?->tingkatan) }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="X / XI / XII (opsional)" />
        <x-input-error :messages="$errors->get('tingkatan')" class="text-sm text-red-600" />
    </div>
</div>

<button type="submit" class="w-full rounded-2xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-blue-500">
    {{ $submit }}
</button>

