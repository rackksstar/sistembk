<div class="space-y-2">
    <label class="block text-sm font-semibold text-slate-900" for="name-{{ $user?->id ?? 'create' }}">Nama</label>
    <input id="name-{{ $user?->id ?? 'create' }}" name="name" value="{{ old('name', $user?->name) }}" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100" />
    <x-input-error :messages="$errors->get('name')" class="text-sm text-red-600" />
</div>

<div class="space-y-2">
    <label class="block text-sm font-semibold text-slate-900" for="email-{{ $user?->id ?? 'create' }}">Email</label>
    <input id="email-{{ $user?->id ?? 'create' }}" name="email" type="email" value="{{ old('email', $user?->email) }}" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100" />
    <x-input-error :messages="$errors->get('email')" class="text-sm text-red-600" />
</div>

<div class="space-y-2">
    <label class="block text-sm font-semibold text-slate-900" for="role-{{ $user?->id ?? 'create' }}">Role</label>
    <select id="role-{{ $user?->id ?? 'create' }}" name="role" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
        @foreach($roles as $item)
            <option value="{{ $item }}" @selected(old('role', $user?->role ?? 'siswa') === $item)>{{ ucfirst($item) }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('role')" class="text-sm text-red-600" />
</div>

<div class="space-y-2">
    <label class="block text-sm font-semibold text-slate-900" for="status-{{ $user?->id ?? 'create' }}">Status Akun</label>
    <select id="status-{{ $user?->id ?? 'create' }}" name="status" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
        @foreach($statuses as $item)
            <option value="{{ $item }}" @selected(old('status', $user?->status ?? \App\Models\User::STATUS_APPROVED) === $item)>{{ ucfirst($item) }}</option>
        @endforeach
    </select>
    <p class="text-xs leading-5 text-slate-500">Gunakan status untuk mengaktifkan, menahan, atau menolak akses akun semua role.</p>
    <x-input-error :messages="$errors->get('status')" class="text-sm text-red-600" />
</div>

<div class="grid gap-4 sm:grid-cols-2">
    <div class="space-y-2">
        <label class="block text-sm font-semibold text-slate-900" for="password-{{ $user?->id ?? 'create' }}">Password</label>
        <input id="password-{{ $user?->id ?? 'create' }}" name="password" type="password" @if(! $user) required @endif class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="{{ $user ? 'Kosongkan jika tetap' : '' }}" />
        <x-input-error :messages="$errors->get('password')" class="text-sm text-red-600" />
    </div>
    <div class="space-y-2">
        <label class="block text-sm font-semibold text-slate-900" for="password-confirm-{{ $user?->id ?? 'create' }}">Konfirmasi</label>
        <input id="password-confirm-{{ $user?->id ?? 'create' }}" name="password_confirmation" type="password" @if(! $user) required @endif class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100" />
    </div>
</div>

<button type="submit" class="w-full rounded-2xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-blue-500">
    {{ $submit }}
</button>
