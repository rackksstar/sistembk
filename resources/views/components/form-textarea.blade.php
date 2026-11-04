@props(['disabled' => false])

<textarea @disabled($disabled) {{ $attributes->merge(['class' => 'ui-input min-h-[120px] resize-y']) }}>{{ $slot }}</textarea>
