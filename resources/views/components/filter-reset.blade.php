@props(['show' => null])

@php
    $hasFilter = $show ?? request()->query() !== [];
@endphp

@if($hasFilter)
    <a href="{{ url()->current() }}" class="ui-btn-secondary">Reset</a>
@endif