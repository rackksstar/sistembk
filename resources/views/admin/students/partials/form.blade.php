<div class="space-y-2">
    <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100">Nama siswa</label>
    <input name="name" value="{{ old('name', $student?->name) }}" required class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/50" placeholder="Nama lengkap" />
    <x-input-error :messages="$errors->get('name')" class="text-sm text-red-600" />
</div>

<div class="space-y-2">
    <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100">NISN</label>
    <input name="nisn" value="{{ old('nisn', $student?->nisn) }}" required class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/50" placeholder="NISN" />
    <x-input-error :messages="$errors->get('nisn')" class="text-sm text-red-600" />
</div>

<div class="space-y-2">
    <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100">Tanggal lahir</label>
    <input name="birth_date" type="date" value="{{ old('birth_date', $student?->birth_date?->format('Y-m-d')) }}" required class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/50" />
    <x-input-error :messages="$errors->get('birth_date')" class="text-sm text-red-600" />
</div>

<div class="space-y-2">
    <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100">Kelas BK</label>
    <select name="kelas_id" required class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/50">
        <option value="">Pilih kelas</option>
        @foreach($kelasList as $kelas)
            <option value="{{ $kelas->id }}" @selected((string) old('kelas_id', $student?->kelas_id) === (string) $kelas->id)>
                {{ $kelas->nama }} ({{ $kelas->sekolah?->nama ?? 'Sekolah' }})
            </option>
        @endforeach
    </select>
    <p class="text-xs text-slate-500 dark:text-slate-400">Wajib. Dipakai tryout, angket, rapor, dan filter sekolah.</p>
    <x-input-error :messages="$errors->get('kelas_id')" class="text-sm text-red-600" />
</div>

<div class="space-y-2">
    <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100">Jenis kelamin</label>
    <select name="jenis_kelamin" class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/50">
        <option value="">Opsional</option>
        <option value="L" @selected(old('jenis_kelamin', $student?->jenis_kelamin) === 'L')>Laki-laki</option>
        <option value="P" @selected(old('jenis_kelamin', $student?->jenis_kelamin) === 'P')>Perempuan</option>
    </select>
    <x-input-error :messages="$errors->get('jenis_kelamin')" class="text-sm text-red-600" />
</div>

<div class="space-y-2">
    <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100">Alamat</label>
    <textarea name="alamat" rows="2" class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/50" placeholder="Opsional">{{ old('alamat', $student?->alamat) }}</textarea>
    <x-input-error :messages="$errors->get('alamat')" class="text-sm text-red-600" />
</div>

@if($student)
    <p class="text-xs text-slate-500 dark:text-slate-400">Status biodata: <span class="font-semibold">{{ $student->status_biodata === 'lengkap' ? 'Lengkap' : 'Belum lengkap' }}</span> (otomatis dari jenis kelamin + alamat)</p>
@endif

<div class="space-y-2">
    <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100">Sekolah (teks legacy)</label>
    <input name="school" value="{{ old('school', $student?->school) }}" class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/50" placeholder="Opsional — utamakan kelas BK di atas" />
</div>

<div class="space-y-2">
    <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100">Akun login siswa</label>
    <select name="user_id" class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/50">
        <option value="">Hubungkan ke akun siswa (opsional)</option>
        @foreach($studentUsers as $user)
            <option value="{{ $user->id }}" @selected((string) old('user_id', $student?->user_id) === (string) $user->id)>{{ $user->name }} - {{ $user->email }}</option>
        @endforeach
    </select>
</div>

<button class="w-full rounded-2xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white hover:bg-blue-500">{{ $submit }}</button>
