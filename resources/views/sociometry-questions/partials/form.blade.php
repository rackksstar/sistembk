@php $questionId = $question?->id ?? 'create'; @endphp

<div class="space-y-2">
    <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100" for="jenis-{{ $questionId }}">Jenis</label>
    <select id="jenis-{{ $questionId }}" name="jenis" required class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm focus:border-blue-400 focus:outline-hidden focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/50">
        @foreach($jenisOptions as $value => $label)
            <option value="{{ $value }}" @selected(old('jenis', $question?->jenis ?? \App\Models\SociometryQuestion::JENIS_POSITIF) === $value)>{{ $label }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('jenis')" class="text-sm text-red-600" />
</div>

<div class="space-y-2">
    <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100" for="aspek-{{ $questionId }}">Aspek</label>
    <select id="aspek-{{ $questionId }}" name="aspek" required class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm focus:border-blue-400 focus:outline-hidden focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/50">
        @foreach($aspekOptions as $value => $label)
            <option value="{{ $value }}" @selected(old('aspek', $question?->aspek ?? \App\Models\SociometryQuestion::ASPEK_PERTEMANAN) === $value)>{{ $label }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('aspek')" class="text-sm text-red-600" />
</div>

<div class="space-y-2">
    <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100" for="pertanyaan-{{ $questionId }}">Teks Pertanyaan</label>
    <textarea id="pertanyaan-{{ $questionId }}" name="pertanyaan" rows="4" required class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm focus:border-blue-400 focus:outline-hidden focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/50" placeholder="Tulis pertanyaan...">{{ old('pertanyaan', $question?->pertanyaan) }}</textarea>
    <x-input-error :messages="$errors->get('pertanyaan')" class="text-sm text-red-600" />
</div>

<div class="grid gap-4 sm:grid-cols-2">
    <div class="space-y-2">
        <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100" for="urutan-{{ $questionId }}">Urutan</label>
        <input id="urutan-{{ $questionId }}" type="number" name="urutan" min="0" value="{{ old('urutan', $question?->urutan ?? 0) }}" class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm focus:border-blue-400 focus:outline-hidden focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/50" />
        <x-input-error :messages="$errors->get('urutan')" class="text-sm text-red-600" />
    </div>

    <div class="space-y-2">
        <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100" for="active-{{ $questionId }}">Status</label>
        <select id="active-{{ $questionId }}" name="is_active" required class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm focus:border-blue-400 focus:outline-hidden focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/50">
            <option value="1" @selected((string) old('is_active', (int) ($question?->is_active ?? true)) === '1')>Aktif</option>
            <option value="0" @selected((string) old('is_active', (int) ($question?->is_active ?? true)) === '0')>Nonaktif</option>
        </select>
        <x-input-error :messages="$errors->get('is_active')" class="text-sm text-red-600" />
    </div>
</div>

<button type="submit" class="w-full rounded-2xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-blue-500">
    {{ $submit }}
</button>
