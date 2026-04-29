<input name="name" value="{{ old('name', $student?->name) }}" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" placeholder="Nama siswa" />
<x-input-error :messages="$errors->get('name')" class="text-sm text-red-600" />

<input name="nisn" value="{{ old('nisn', $student?->nisn) }}" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" placeholder="NISN" />
<x-input-error :messages="$errors->get('nisn')" class="text-sm text-red-600" />

<input name="birth_date" type="date" value="{{ old('birth_date', $student?->birth_date?->format('Y-m-d')) }}" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" />
<x-input-error :messages="$errors->get('birth_date')" class="text-sm text-red-600" />

<input name="school" value="{{ old('school', $student?->school) }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" placeholder="Sekolah" />

<select name="user_id" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
    <option value="">Hubungkan ke akun siswa (opsional)</option>
    @foreach($studentUsers as $user)
        <option value="{{ $user->id }}" @selected((string) old('user_id', $student?->user_id) === (string) $user->id)>{{ $user->name }} - {{ $user->email }}</option>
    @endforeach
</select>

<button class="w-full rounded-2xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white">{{ $submit }}</button>
