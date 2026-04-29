<div class="space-y-2">
    <label class="block text-sm font-semibold text-slate-900" for="title-{{ $career?->id ?? 'create' }}">Judul</label>
    <input id="title-{{ $career?->id ?? 'create' }}" name="title" value="{{ old('title', $career?->title) }}" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100" />
    <x-input-error :messages="$errors->get('title')" class="text-sm text-red-600" />
</div>

<div class="space-y-2">
    <label class="block text-sm font-semibold text-slate-900" for="category-{{ $career?->id ?? 'create' }}">Kategori</label>
    <input id="category-{{ $career?->id ?? 'create' }}" name="category" value="{{ old('category', $career?->category) }}" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="Contoh: Teknologi" />
    <x-input-error :messages="$errors->get('category')" class="text-sm text-red-600" />
</div>

<div class="space-y-2">
    <label class="block text-sm font-semibold text-slate-900" for="description-{{ $career?->id ?? 'create' }}">Deskripsi</label>
    <textarea id="description-{{ $career?->id ?? 'create' }}" name="description" rows="5" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">{{ old('description', $career?->description) }}</textarea>
    <x-input-error :messages="$errors->get('description')" class="text-sm text-red-600" />
</div>

<div class="space-y-2">
    <label class="block text-sm font-semibold text-slate-900" for="image-{{ $career?->id ?? 'create' }}">Gambar</label>
    <input id="image-{{ $career?->id ?? 'create' }}" name="image" type="file" accept="image/*" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm file:mr-4 file:rounded-xl file:border-0 file:bg-blue-600 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100" />
    @if($career?->image_path)
        <p class="text-xs text-slate-500">Gambar saat ini akan tetap dipakai jika tidak memilih file baru.</p>
    @endif
    <x-input-error :messages="$errors->get('image')" class="text-sm text-red-600" />
</div>

<button type="submit" class="w-full rounded-2xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-blue-500">
    {{ $submit }}
</button>
