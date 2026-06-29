<input name="name" value="{{ old('name', $student?->name) }}" required class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm" placeholder="Nama siswa" />
<x-input-error :messages="$errors->get('name')" class="text-sm text-red-600" />

<input name="nisn" value="{{ old('nisn', $student?->nisn) }}" required class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm" placeholder="NISN" />
<x-input-error :messages="$errors->get('nisn')" class="text-sm text-red-600" />

<input name="birth_date" type="date" value="{{ old('birth_date', $student?->birth_date?->format('Y-m-d')) }}" required class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm" />
<x-input-error :messages="$errors->get('birth_date')" class="text-sm text-red-600" />

<select name="kelas_id" required class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm">
    <option value="">Pilih kelas</option>
    @foreach($kelasList as $kelas)
        <option value="{{ $kelas->id }}" @selected((string) old('kelas_id', $student?->kelas_id) === (string) $kelas->id)>
            {{ $kelas->nama }} ({{ $kelas->sekolah?->nama ?? 'Sekolah' }})
        </option>
    @endforeach
</select>
<x-input-error :messages="$errors->get('kelas_id')" class="text-sm text-red-600" />

<select name="jenis_kelamin" class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm">
    <option value="">Jenis kelamin (opsional)</option>
    <option value="L" @selected(old('jenis_kelamin', $student?->jenis_kelamin) === 'L')>Laki-laki</option>
    <option value="P" @selected(old('jenis_kelamin', $student?->jenis_kelamin) === 'P')>Perempuan</option>
</select>
<x-input-error :messages="$errors->get('jenis_kelamin')" class="text-sm text-red-600" />

<textarea name="alamat" rows="2" class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm" placeholder="Alamat (opsional)">{{ old('alamat', $student?->alamat) }}</textarea>
<x-input-error :messages="$errors->get('alamat')" class="text-sm text-red-600" />

@if($student)
    <p class="text-xs text-slate-500 dark:text-slate-400">Status biodata: <span class="font-semibold">{{ $student->status_biodata === 'lengkap' ? 'Lengkap' : 'Belum lengkap' }}</span> (otomatis dari jenis kelamin + alamat)</p>
@endif

<input name="school" value="{{ old('school', $student?->school) }}" class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm" placeholder="Sekolah (opsional, legacy)" />

<select name="user_id" class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm">
    <option value="">Hubungkan ke akun siswa (opsional)</option>
    @foreach($studentUsers as $user)
        <option value="{{ $user->id }}" @selected((string) old('user_id', $student?->user_id) === (string) $user->id)>{{ $user->name }} - {{ $user->email }}</option>
    @endforeach
</select>

<button class="w-full rounded-2xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white">{{ $submit }}</button>
