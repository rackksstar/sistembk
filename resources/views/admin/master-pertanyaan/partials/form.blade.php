<div class="space-y-2">
    <label class="block text-sm font-semibold text-slate-900" for="kategori-{{ $question?->id ?? 'create' }}">Kategori</label>
    <select id="kategori-{{ $question?->id ?? 'create' }}" name="kategori" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
        @foreach($kategoriOptions as $item)
            <option value="{{ $item }}" @selected(old('kategori', $question?->kategori ?? \App\Models\MasterQuestion::KATEGORI_ANGKET) === $item)>{{ strtoupper($item) }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('kategori')" class="text-sm text-red-600" />
</div>

<div class="space-y-2">
    <label class="block text-sm font-semibold text-slate-900" for="tipe-{{ $question?->id ?? 'create' }}">Tipe Input</label>
    <select id="tipe-{{ $question?->id ?? 'create' }}" name="tipe_input" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
        @foreach($tipeOptions as $item)
            <option value="{{ $item }}" @selected(old('tipe_input', $question?->tipe_input ?? \App\Models\MasterQuestion::TIPE_SKALA) === $item)>{{ str_replace('_', ' ', $item) }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('tipe_input')" class="text-sm text-red-600" />
</div>

<div class="space-y-2">
    <label class="block text-sm font-semibold text-slate-900" for="teks-{{ $question?->id ?? 'create' }}">Teks Pertanyaan</label>
    <textarea id="teks-{{ $question?->id ?? 'create' }}" name="teks_pertanyaan" rows="4" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="Tulis pertanyaan...">{{ old('teks_pertanyaan', $question?->teks_pertanyaan) }}</textarea>
    <x-input-error :messages="$errors->get('teks_pertanyaan')" class="text-sm text-red-600" />
</div>

<div class="space-y-2">
    <label class="block text-sm font-semibold text-slate-900" for="active-{{ $question?->id ?? 'create' }}">Status</label>
    <select id="active-{{ $question?->id ?? 'create' }}" name="is_active" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
        <option value="1" @selected((string) old('is_active', (int) ($question?->is_active ?? true)) === '1')>Aktif</option>
        <option value="0" @selected((string) old('is_active', (int) ($question?->is_active ?? true)) === '0')>Nonaktif</option>
    </select>
    <x-input-error :messages="$errors->get('is_active')" class="text-sm text-red-600" />
</div>

<button type="submit" class="w-full rounded-2xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-blue-500">
    {{ $submit }}
</button>

