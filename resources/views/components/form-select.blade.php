@props([
    'disabled' => false,
    'select2' => true,
])

<select
    @disabled($disabled)
    {{ $attributes->class([
        'ui-input',
        'js-select2' => $select2,
    ]) }}
    @if ($select2 && ! $attributes->has('data-placeholder'))
        data-placeholder="Pilih opsi"
    @endif
>
    {{ $slot }}
</select>
