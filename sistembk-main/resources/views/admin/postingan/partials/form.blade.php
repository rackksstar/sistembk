<div class="space-y-2">
    <x-input-label for="post_category_id" value="Kategori" />
    <x-form-select id="post_category_id" name="post_category_id" required>
        <option value="">Pilih kategori</option>
        @foreach($kategoris as $kat)
            <option value="{{ $kat->id }}" @selected((int) old('post_category_id', $item?->post_category_id) === $kat->id)>{{ $kat->name }}</option>
        @endforeach
    </x-form-select>
    <x-input-error :messages="$errors->get('post_category_id')" />
</div>

<div class="space-y-2">
    <x-input-label for="judul" value="Judul" />
    <x-text-input id="judul" name="judul" type="text" class="w-full" :value="old('judul', $item?->judul)" required />
    <x-input-error :messages="$errors->get('judul')" />
</div>

<div class="space-y-2">
    <x-input-label for="isi" value="Isi artikel" />
    <x-form-textarea id="isi" name="isi" rows="6" required>{{ old('isi', $item?->isi) }}</x-form-textarea>
    <x-input-error :messages="$errors->get('isi')" />
</div>

<div class="space-y-2">
    <x-input-label for="status" value="Status" />
    <x-form-select id="status" name="status">
        @foreach(\App\Models\Postingan::STATUSES as $value => $label)
            <option value="{{ $value }}" @selected(old('status', $item?->status ?? \App\Models\Postingan::STATUS_DRAFT) === $value)>{{ $label }}</option>
        @endforeach
    </x-form-select>
    <x-input-error :messages="$errors->get('status')" />
</div>

<div class="space-y-2">
    <x-input-label for="gambar" value="Gambar (opsional)" />
    <input type="file" id="gambar" name="gambar" accept="image/*" class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm text-slate-600 dark:text-slate-400 file:mr-3 file:rounded-xl file:border-0 file:bg-blue-50 dark:bg-blue-950/40 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-blue-700 dark:text-blue-300">
    <x-input-error :messages="$errors->get('gambar')" />
</div>

<x-primary-button class="w-full justify-center">{{ $submit }}</x-primary-button>
