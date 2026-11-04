@php
    $suffix = $question?->id ?? 'create';
    $selectedTipe = old('tipe_input', $question?->tipe_input ?? \App\Models\MasterQuestion::TIPE_SKALA);
    $opsiAwal = old('opsi', $question?->opsiList() ?: ['', '']);
    if (! is_array($opsiAwal) || count($opsiAwal) < 2) {
        $opsiAwal = array_pad(is_array($opsiAwal) ? $opsiAwal : [], 2, '');
    }
@endphp

<div
    class="space-y-4"
    x-data="{
        tipe: @js($selectedTipe),
        opsi: @js(array_values($opsiAwal)),
        kunci: @js(old('kunci_jawaban', $question?->kunci_jawaban)),
        addOpsi() { this.opsi.push('') },
        removeOpsi(i) { if (this.opsi.length > 2) this.opsi.splice(i, 1) }
    }"
>
    <div class="space-y-2">
        <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100" for="kategori-{{ $suffix }}">Kategori</label>
        <select id="kategori-{{ $suffix }}" name="kategori" required class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm focus:border-blue-400 focus:outline-hidden focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/50">
            @foreach($kategoriOptions as $item)
                <option value="{{ $item }}" @selected(old('kategori', $question?->kategori ?? \App\Models\MasterQuestion::KATEGORI_ANGKET) === $item)>{{ strtoupper($item) }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('kategori')" class="text-sm text-red-600" />
    </div>

    <div class="space-y-2">
        <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100" for="tipe-{{ $suffix }}">Tipe Input</label>
        <select
            id="tipe-{{ $suffix }}"
            name="tipe_input"
            required
            x-model="tipe"
            class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm focus:border-blue-400 focus:outline-hidden focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/50"
        >
            @foreach($tipeOptions as $item)
                <option value="{{ $item }}">{{ \App\Models\MasterQuestion::TIPE_LABELS[$item] ?? str_replace('_', ' ', $item) }}</option>
            @endforeach
        </select>
        <p class="text-xs text-slate-500 dark:text-slate-400">
            Skala: pilihan 1–5 · Pilihan ganda: radio opsi · Isian: teks esai (nilai tryout dinilai Guru BK).
        </p>
        <x-input-error :messages="$errors->get('tipe_input')" class="text-sm text-red-600" />
    </div>

    <div class="space-y-2">
        <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100" for="teks-{{ $suffix }}">Teks Pertanyaan</label>
        <textarea id="teks-{{ $suffix }}" name="teks_pertanyaan" rows="4" required class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm focus:border-blue-400 focus:outline-hidden focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/50" placeholder="Tulis pertanyaan...">{{ old('teks_pertanyaan', $question?->teks_pertanyaan) }}</textarea>
        <x-input-error :messages="$errors->get('teks_pertanyaan')" class="text-sm text-red-600" />
    </div>

    <div x-show="tipe === 'pilihan_ganda'" x-cloak class="space-y-3 rounded-2xl border border-indigo-100 bg-indigo-50/60 p-4 dark:border-indigo-900/40 dark:bg-indigo-950/20">
        <div class="flex items-center justify-between gap-3">
            <p class="text-sm font-semibold text-indigo-900 dark:text-indigo-200">Opsi pilihan ganda</p>
            <button type="button" x-on:click="addOpsi()" class="rounded-xl bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500">+ Opsi</button>
        </div>
        <template x-for="(item, index) in opsi" :key="index">
            <div class="flex gap-2">
                <input
                    type="text"
                    :name="'opsi['+index+']'"
                    x-model="opsi[index]"
                    class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900"
                    :placeholder="'Opsi '+(index+1)"
                >
                <button type="button" x-on:click="removeOpsi(index)" class="rounded-xl border border-slate-200 px-3 text-xs font-semibold text-slate-600 hover:bg-white dark:border-slate-700 dark:text-slate-300">Hapus</button>
            </div>
        </template>
        <x-input-error :messages="$errors->get('opsi')" class="text-sm text-red-600" />

        <div class="space-y-2">
            <label class="block text-sm font-semibold text-indigo-900 dark:text-indigo-200" for="kunci-{{ $suffix }}">Kunci jawaban (opsional)</label>
            <select
                id="kunci-{{ $suffix }}"
                name="kunci_jawaban"
                x-model="kunci"
                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900"
            >
                <option value="">Tanpa kunci — dinilai manual</option>
                <template x-for="(item, index) in opsi.filter(o => (o || '').trim() !== '')" :key="'k'+index">
                    <option :value="item" x-text="item"></option>
                </template>
            </select>
            <p class="text-xs text-indigo-800/80 dark:text-indigo-300/80">Jika diisi, tryout menilai otomatis 100/0. Kosongkan untuk dinilai Guru BK.</p>
            <x-input-error :messages="$errors->get('kunci_jawaban')" class="text-sm text-red-600" />
        </div>
    </div>

    <div class="space-y-2">
        <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100" for="active-{{ $suffix }}">Status</label>
        <select id="active-{{ $suffix }}" name="is_active" required class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm focus:border-blue-400 focus:outline-hidden focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/50">
            <option value="1" @selected((string) old('is_active', (int) ($question?->is_active ?? true)) === '1')>Aktif</option>
            <option value="0" @selected((string) old('is_active', (int) ($question?->is_active ?? true)) === '0')>Nonaktif</option>
        </select>
        <x-input-error :messages="$errors->get('is_active')" class="text-sm text-red-600" />
    </div>

    <button type="submit" class="w-full rounded-2xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-blue-500">
        {{ $submit }}
    </button>
</div>
