
@props([
    'name',
    'value' => '',
    'id' => 'ckeditor',
    'errorName' => null,
])

<textarea
    name="{{ $name }}"
    id="{{ $id }}"
    data-ckeditor
    {{ $attributes->except(['id'])->class(['form-control', 'is-invalid' => $errors->has($errorName ?? $name)]) }}
>{{ old($errorName ?? $name, $value) }}</textarea>

@error($errorName ?? $name)
    <div class="invalid-feedback d-block">
        {{ $message }}
    </div>
@enderror
