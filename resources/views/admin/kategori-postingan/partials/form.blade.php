<div class="space-y-2">
    <label class="block text-sm font-semibold text-slate-900" for="name-{{ $kategori?->id ?? 'create' }}">Nama Kategori</label>
    <input id="name-{{ $kategori?->id ?? 'create' }}" name="name" value="{{ old('name', $kategori?->name) }}" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="Motivasi / Karier / ..." />
    <x-input-error :messages="$errors->get('name')" class="text-sm text-red-600" />
</div>

<button type="submit" class="w-full rounded-2xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-blue-500">
    {{ $submit }}
</button>

