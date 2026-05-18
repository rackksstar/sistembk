@php
    $user = $guruBk?->user;
@endphp

<div class="space-y-2">
    <label class="block text-sm font-semibold text-slate-900" for="name-{{ $guruBk?->id ?? 'create' }}">Nama</label>
    <input id="name-{{ $guruBk?->id ?? 'create' }}" name="name" value="{{ old('name', $user?->name) }}" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100" />
    <x-input-error :messages="$errors->get('name')" class="text-sm text-red-600" />
</div>

<div class="space-y-2">
    <label class="block text-sm font-semibold text-slate-900" for="email-{{ $guruBk?->id ?? 'create' }}">Email</label>
    <input id="email-{{ $guruBk?->id ?? 'create' }}" name="email" type="email" value="{{ old('email', $user?->email) }}" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100" />
    <x-input-error :messages="$errors->get('email')" class="text-sm text-red-600" />
</div>

<div class="grid gap-4 sm:grid-cols-2">
    <div class="space-y-2">
        <label class="block text-sm font-semibold text-slate-900" for="password-{{ $guruBk?->id ?? 'create' }}">Password</label>
        <input id="password-{{ $guruBk?->id ?? 'create' }}" name="password" type="password" @if(! $guruBk) required @endif class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="{{ $guruBk ? 'Kosongkan jika tetap' : '' }}" />
        <x-input-error :messages="$errors->get('password')" class="text-sm text-red-600" />
    </div>
    <div class="space-y-2">
        <label class="block text-sm font-semibold text-slate-900" for="password-confirm-{{ $guruBk?->id ?? 'create' }}">Konfirmasi</label>
        <input id="password-confirm-{{ $guruBk?->id ?? 'create' }}" name="password_confirmation" type="password" @if(! $guruBk) required @endif class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100" />
    </div>
</div>

<div class="space-y-2">
    <label class="block text-sm font-semibold text-slate-900" for="status-{{ $guruBk?->id ?? 'create' }}">Status Akun</label>
    <select id="status-{{ $guruBk?->id ?? 'create' }}" name="status" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
        @foreach($statuses as $item)
            <option value="{{ $item }}" @selected(old('status', $user?->status ?? \App\Models\User::STATUS_APPROVED) === $item)>{{ ucfirst($item) }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('status')" class="text-sm text-red-600" />
</div>

<div class="space-y-2">
    <label class="block text-sm font-semibold text-slate-900" for="sekolah-{{ $guruBk?->id ?? 'create' }}">Sekolah</label>
    <select id="sekolah-{{ $guruBk?->id ?? 'create' }}" name="sekolah_id" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
        <option value="">(Opsional)</option>
        @foreach($sekolahs as $s)
            <option value="{{ $s->id }}" @selected((string) old('sekolah_id', $guruBk?->sekolah_id) === (string) $s->id)>{{ $s->nama }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('sekolah_id')" class="text-sm text-red-600" />
</div>

<div class="grid gap-4 sm:grid-cols-2">
    <div class="space-y-2">
        <label class="block text-sm font-semibold text-slate-900" for="nip-{{ $guruBk?->id ?? 'create' }}">NIP</label>
        <input id="nip-{{ $guruBk?->id ?? 'create' }}" name="nip" value="{{ old('nip', $guruBk?->nip) }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100" />
        <x-input-error :messages="$errors->get('nip')" class="text-sm text-red-600" />
    </div>
    <div class="space-y-2">
        <label class="block text-sm font-semibold text-slate-900" for="jabatan-{{ $guruBk?->id ?? 'create' }}">Jabatan</label>
        <input id="jabatan-{{ $guruBk?->id ?? 'create' }}" name="jabatan" value="{{ old('jabatan', $guruBk?->jabatan) }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100" />
        <x-input-error :messages="$errors->get('jabatan')" class="text-sm text-red-600" />
    </div>
</div>

<div class="space-y-2">
    <label class="block text-sm font-semibold text-slate-900" for="bidang-{{ $guruBk?->id ?? 'create' }}">Bidang Studi</label>
    <input id="bidang-{{ $guruBk?->id ?? 'create' }}" name="bidang_studi" value="{{ old('bidang_studi', $guruBk?->bidang_studi) }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100" />
    <x-input-error :messages="$errors->get('bidang_studi')" class="text-sm text-red-600" />
</div>

<button type="submit" class="w-full rounded-2xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-blue-500">
    {{ $submit }}
</button>

