<input name="name" value="{{ old('name', $class?->name) }}" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" placeholder="Nama kelas" />
<x-input-error :messages="$errors->get('name')" class="text-sm text-red-600" />

<textarea name="description" rows="4" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" placeholder="Deskripsi kelas">{{ old('description', $class?->description) }}</textarea>
<x-input-error :messages="$errors->get('description')" class="text-sm text-red-600" />

<button class="w-full rounded-2xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white">{{ $submit }}</button>
