@php
    $selectedKelas = collect(old('kelas_ids', $tryout?->kelas->pluck('id')->all() ?? []));
    $selectedSoal = collect(old('soal_ids', $tryout?->soal_ids ?? []));
    $locked = $locked ?? false;
@endphp

<div class="space-y-2">
    <x-input-label for="judul" value="Judul tryout" />
    <x-text-input id="judul" name="judul" type="text" class="w-full" :value="old('judul', $tryout?->judul)" required />
    <x-input-error :messages="$errors->get('judul')" />
</div>

<div class="space-y-2">
    <x-input-label for="deskripsi" value="Deskripsi" />
    <x-form-textarea id="deskripsi" name="deskripsi" rows="2">{{ old('deskripsi', $tryout?->deskripsi) }}</x-form-textarea>
    <x-input-error :messages="$errors->get('deskripsi')" />
</div>

<div class="grid gap-4 sm:grid-cols-3">
    <div class="space-y-2">
        <x-input-label for="durasi_menit" value="Durasi (menit)" />
        <x-text-input id="durasi_menit" name="durasi_menit" type="number" class="w-full" min="5" max="180" :value="old('durasi_menit', $tryout?->durasi_menit ?? 60)" required />
        <x-input-error :messages="$errors->get('durasi_menit')" />
    </div>
    <div class="space-y-2">
        <x-input-label for="mulai_at" value="Mulai" />
        <x-text-input
            id="mulai_at"
            name="mulai_at"
            type="datetime-local"
            class="w-full"
            :value="old('mulai_at', $tryout?->mulai_at?->format('Y-m-d\TH:i'))"
            required
        />
        <x-input-error :messages="$errors->get('mulai_at')" />
    </div>
    <div class="space-y-2">
        <x-input-label for="selesai_at" value="Selesai" />
        <x-text-input
            id="selesai_at"
            name="selesai_at"
            type="datetime-local"
            class="w-full"
            :value="old('selesai_at', $tryout?->selesai_at?->format('Y-m-d\TH:i'))"
            required
        />
        <x-input-error :messages="$errors->get('selesai_at')" />
    </div>
</div>

<div class="space-y-2">
    <x-input-label for="status" value="Status" />
    <x-form-select id="status" name="status" required>
        @foreach(\App\Models\TryOut::STATUSES as $value => $label)
            <option value="{{ $value }}" @selected(old('status', $tryout?->status ?? \App\Models\TryOut::STATUS_AKTIF) === $value)>{{ $label }}</option>
        @endforeach
    </x-form-select>
    <x-input-error :messages="$errors->get('status')" />
</div>

<div class="space-y-2">
    <x-input-label value="Kelas yang diikutkan" />
    @if($locked)
        <p class="rounded-2xl border border-amber-100 bg-amber-50 dark:bg-amber-950/40 px-4 py-3 text-sm text-amber-800 dark:text-amber-300">
            Kelas tidak dapat diubah karena sudah ada siswa yang mengumpulkan jawaban.
        </p>
        <div class="grid gap-2 sm:grid-cols-2">
            @foreach($tryout->kelas as $k)
                <div class="rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/60 px-4 py-2 text-sm text-slate-700 dark:text-slate-300">{{ $k->nama }}</div>
            @endforeach
        </div>
    @else
        <div class="grid gap-2 sm:grid-cols-2">
            @forelse($kelas as $k)
                <label class="flex items-center gap-2 rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/60 px-4 py-2 text-sm">
                    <input type="checkbox" name="kelas_ids[]" value="{{ $k->id }}" @checked($selectedKelas->contains($k->id))>
                    <span>{{ $k->nama }}</span>
                </label>
            @empty
                <p class="text-sm text-slate-500 dark:text-slate-400">Belum ada kelas di sekolah Anda.</p>
            @endforelse
        </div>
        <x-input-error :messages="$errors->get('kelas_ids')" />
    @endif
</div>

<div class="space-y-2">
    <x-input-label value="Soal tryout (master)" />
    @if($locked)
        <p class="rounded-2xl border border-amber-100 bg-amber-50 dark:bg-amber-950/40 px-4 py-3 text-sm text-amber-800 dark:text-amber-300">
            Daftar soal tidak dapat diubah setelah ada pengumpulan jawaban.
        </p>
        <div class="max-h-64 space-y-2 overflow-y-auto rounded-2xl border border-slate-200 dark:border-slate-700 p-4">
            @foreach($soal->whereIn('id', $tryout->soal_ids ?? []) as $s)
                <p class="text-sm text-slate-700 dark:text-slate-300">{{ $s->teks_pertanyaan }} <span class="text-xs text-slate-400">({{ $s->tipe_input }})</span></p>
            @endforeach
        </div>
    @else
        <div class="max-h-64 space-y-2 overflow-y-auto rounded-2xl border border-slate-200 dark:border-slate-700 p-4">
            @forelse($soal as $s)
                <label class="flex gap-2 text-sm text-slate-700 dark:text-slate-300">
                    <input type="checkbox" name="soal_ids[]" value="{{ $s->id }}" @checked($selectedSoal->contains($s->id))>
                    <span>{{ $s->teks_pertanyaan }} <span class="text-xs text-slate-400">({{ $s->tipe_input }})</span></span>
                </label>
            @empty
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Belum ada soal tryout aktif.
                    @if(auth()->user()?->role === 'admin')
                        Tambahkan di menu Master Pertanyaan (kategori tryout).
                    @else
                        Minta admin menambahkan soal kategori tryout di Master Pertanyaan.
                    @endif
                </p>
            @endforelse
        </div>
        <x-input-error :messages="$errors->get('soal_ids')" />
    @endif
</div>

<div class="flex flex-wrap gap-3">
    <x-primary-button>{{ $submit }}</x-primary-button>
    <a href="{{ route('guru.tryout.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-5 py-3 text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60">Batal</a>
</div>
