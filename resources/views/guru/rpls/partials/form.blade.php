<div class="grid gap-4">
    <div class="grid gap-4 md:grid-cols-2">
        <div class="space-y-2">
            <x-input-label for="title_{{ $rpl?->id ?? 'new' }}" value="Judul RPL" />
            <x-text-input id="title_{{ $rpl?->id ?? 'new' }}" name="title" value="{{ old('title', $rpl?->title) }}" required />
        </div>
        <div class="space-y-2">
            <x-input-label for="type_{{ $rpl?->id ?? 'new' }}" value="Jenis RPL" />
            <x-form-select id="type_{{ $rpl?->id ?? 'new' }}" name="type" required>
                <option value="">Pilih jenis</option>
                @foreach($types as $value => $label)
                    <option value="{{ $value }}" @selected(old('type', $rpl?->type) === $value)>{{ $label }}</option>
                @endforeach
            </x-form-select>
        </div>
        <div class="space-y-2">
            <x-input-label for="service_date_{{ $rpl?->id ?? 'new' }}" value="Tanggal Layanan" />
            <x-text-input id="service_date_{{ $rpl?->id ?? 'new' }}" name="service_date" type="date" value="{{ old('service_date', $rpl?->service_date?->format('Y-m-d')) }}" />
        </div>
        <div class="space-y-2">
            <x-input-label for="target_{{ $rpl?->id ?? 'new' }}" value="Sasaran" />
            <x-text-input id="target_{{ $rpl?->id ?? 'new' }}" name="target" value="{{ old('target', $rpl?->target) }}" placeholder="Contoh: Siswa kelas XI / Andi" />
        </div>
    </div>

    @foreach(['tujuan' => 'Tujuan', 'materi' => 'Materi', 'metode' => 'Metode', 'evaluasi' => 'Evaluasi'] as $field => $label)
        <div class="space-y-2">
            <x-input-label for="{{ $field }}_{{ $rpl?->id ?? 'new' }}" :value="$label" />
            <textarea id="{{ $field }}_{{ $rpl?->id ?? 'new' }}" name="{{ $field }}" rows="4" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">{{ old($field, $rpl?->{$field}) }}</textarea>
        </div>
    @endforeach

    <x-primary-button>{{ $submit }}</x-primary-button>
</div>
