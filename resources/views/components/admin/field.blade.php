@props(['name', 'label', 'type' => 'text', 'value' => null, 'hint' => null, 'required' => false, 'rows' => 4, 'counter' => null])
@php($current = old($name, $value))
<div {{ $attributes->only('class')->merge(['class' => 'fld' . ($errors->has($name) ? ' has-error' : '')]) }}>
    <label for="f-{{ $name }}">{{ $label }} @if ($required)<span class="req">*</span>@endif</label>

    @if ($type === 'textarea')
        <textarea id="f-{{ $name }}" name="{{ $name }}" rows="{{ $rows }}" {{ $attributes->except('class') }} @required($required) @if ($counter) data-counter="{{ $counter }}" @endif>{{ $current }}</textarea>
    @elseif ($type === 'select')
        <select id="f-{{ $name }}" name="{{ $name }}" {{ $attributes->except('class') }} @required($required)>{{ $slot }}</select>
    @else
        <input id="f-{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ $current }}" {{ $attributes->except('class') }} @required($required) @if ($counter) data-counter="{{ $counter }}" @endif>
    @endif

    @if ($hint)<p class="fld__hint">{{ $hint }}</p>@endif
    @if ($counter)<p class="fld__count" data-counter-for="f-{{ $name }}" aria-live="polite"></p>@endif
    @error($name)<p class="fld__error">{{ $message }}</p>@enderror
</div>
