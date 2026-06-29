@props(['disabled' => false])

<select @disabled($disabled) {{ $attributes->merge(['class' => 'ui-input']) }}>
    {{ $slot }}
</select>
