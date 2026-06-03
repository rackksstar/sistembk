<div class="space-y-2">
    <x-input-label for="post_category_id" value="Kategori" />
    <select id="post_category_id" name="post_category_id" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm">
        <option value="">Pilih kategori</option>
        @foreach($kategoris as $kat)
            <option value="{{ $kat->id }}" @selected((int) old('post_category_id', $item?->post_category_id) === $kat->id)>{{ $kat->name }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('post_category_id')" />
</div>

<div class="space-y-2">
    <x-input-label for="judul" value="Judul" />
    <input type="text" id="judul" name="judul" value="{{ old('judul', $item?->judul) }}" required
        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm">
    <x-input-error :messages="$errors->get('judul')" />
</div>

<div class="space-y-2">
    <x-input-label for="isi" value="Isi artikel" />
    <textarea id="isi" name="isi" rows="6" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">{{ old('isi', $item?->isi) }}</textarea>
    <x-input-error :messages="$errors->get('isi')" />
</div>

<div class="space-y-2">
    <x-input-label for="status" value="Status" />
    <select id="status" name="status" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm">
        @foreach(\App\Models\Postingan::STATUSES as $value => $label)
            <option value="{{ $value }}" @selected(old('status', $item?->status ?? \App\Models\Postingan::STATUS_DRAFT) === $value)>{{ $label }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('status')" />
</div>

<div class="space-y-2">
    <x-input-label for="gambar" value="Gambar (opsional)" />
    <input type="file" id="gambar" name="gambar" accept="image/*" class="w-full text-sm text-slate-600">
    <x-input-error :messages="$errors->get('gambar')" />
</div>

<button type="submit" class="w-full rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-500">{{ $submit }}</button>
