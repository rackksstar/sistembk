<div class="space-y-2">
    <label class="block text-sm font-semibold text-slate-900" for="name-{{ $student?->id ?? 'create' }}">Nama Siswa</label>
    <input id="name-{{ $student?->id ?? 'create' }}" name="name" value="{{ old('name', $student?->name) }}" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="Nama lengkap siswa" />
    <x-input-error :messages="$errors->get('name')" class="text-sm text-red-600" />
</div>

<div class="space-y-2">
    <label class="block text-sm font-semibold text-slate-900" for="nisn-{{ $student?->id ?? 'create' }}">NISN</label>
    <input id="nisn-{{ $student?->id ?? 'create' }}" name="nisn" value="{{ old('nisn', $student?->nisn) }}" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="NISN siswa" />
    <x-input-error :messages="$errors->get('nisn')" class="text-sm text-red-600" />
</div>

<div class="space-y-2">
    <label class="block text-sm font-semibold text-slate-900" for="birth-date-{{ $student?->id ?? 'create' }}">Tanggal Lahir</label>
    <input id="birth-date-{{ $student?->id ?? 'create' }}" name="birth_date" type="date" value="{{ old('birth_date', $student?->birth_date?->format('Y-m-d')) }}" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100" />
    <x-input-error :messages="$errors->get('birth_date')" class="text-sm text-red-600" />
</div>

<div class="space-y-2">
    <label class="block text-sm font-semibold text-slate-900" for="school-{{ $student?->id ?? 'create' }}">Sekolah</label>
    <input id="school-{{ $student?->id ?? 'create' }}" name="school" value="{{ old('school', $student?->school) }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="Nama sekolah" />
    <x-input-error :messages="$errors->get('school')" class="text-sm text-red-600" />
</div>

<button class="w-full rounded-2xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white hover:bg-blue-500">{{ $submit }}</button>
