@props(['name', 'label', 'checked' => false, 'hint' => null])
{{-- The hidden "0" makes an unticked box submit false, and lets old() restore it correctly after a validation error. --}}
<div {{ $attributes->only('class')->merge(['class' => 'fld fld--check']) }}>
    <input type="hidden" name="{{ $name }}" value="0">
    <label class="check">
        <input type="checkbox" name="{{ $name }}" value="1" @checked(filter_var(old($name, $checked), FILTER_VALIDATE_BOOLEAN))>
        <span>{{ $label }}</span>
    </label>
    @if ($hint)<p class="fld__hint">{{ $hint }}</p>@endif
</div>
