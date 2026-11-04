@props([
    'question',
    'name' => null,
    'value' => null,
    'required' => true,
    'idPrefix' => 'soal',
])

@php
    $fieldName = $name ?? 'jawaban['.$question->id.']';
    $fieldId = $idPrefix.'_'.$question->id;
    $oldValue = old(str_replace(['[', ']'], ['.', ''], $fieldName), $value);
    $tipe = $question->tipe_input;
    $opsi = collect($question->opsi ?? [])->filter(fn ($item) => filled($item))->values();
@endphp

<div {{ $attributes->merge(['class' => 'mt-4 space-y-2']) }}>
    @if($tipe === \App\Models\MasterQuestion::TIPE_SKALA)
        <x-form-select :id="$fieldId" :name="$fieldName" :required="$required">
            <option value="">Pilih skor 1–5</option>
            @for($i = 1; $i <= 5; $i++)
                <option value="{{ $i }}" @selected((string) $oldValue === (string) $i)>{{ $i }}</option>
            @endfor
        </x-form-select>
    @elseif($tipe === \App\Models\MasterQuestion::TIPE_PILIHAN_GANDA)
        @if($opsi->isEmpty())
            <p class="rounded-2xl bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:bg-amber-950/40 dark:text-amber-200">
                Opsi pilihan ganda belum diatur. Hubungi Guru BK / Admin.
            </p>
        @else
            <div class="space-y-2">
                @foreach($opsi as $index => $label)
                    <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                        <input
                            type="radio"
                            id="{{ $fieldId }}_{{ $index }}"
                            name="{{ $fieldName }}"
                            value="{{ $label }}"
                            @checked((string) $oldValue === (string) $label)
                            @required($required && $loop->first)
                            class="border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-600"
                        >
                        <span>{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        @endif
    @else
        <x-form-textarea
            :id="$fieldId"
            :name="$fieldName"
            rows="3"
            :required="$required"
            placeholder="Tulis jawabanmu"
        >{{ $oldValue }}</x-form-textarea>
    @endif

    <x-input-error :messages="$errors->get(str_replace(['[', ']'], ['.', ''], $fieldName))" />
</div>
