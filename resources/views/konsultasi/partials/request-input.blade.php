@php
    $type = $type ?? 'text';
    $required = $required ?? false;
    $attributes = collect(['min', 'step'])->mapWithKeys(fn ($attribute) => isset($$attribute) ? [$attribute => $$attribute] : []);
@endphp
<div>
    <label for="{{ $name }}" class="mb-2 block text-sm font-semibold text-slate-800">{{ $label }}</label>
    <input type="{{ $type }}" id="{{ $name }}" name="{{ $name }}" value="{{ $value }}" placeholder="{{ $placeholder ?? $label }}" {{ $required ? 'required' : '' }}
        @foreach($attributes as $attribute => $attributeValue) {{ $attribute }}="{{ $attributeValue }}" @endforeach class="request-control">
    @error($name) <p class="request-error">{{ $message }}</p> @enderror
</div>
