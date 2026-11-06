<div class="mb-3" data-contact-row>
    <div class="row gx-2 gy-2 align-items-start">
        <div class="col-12 col-sm">
            <label class="form-label" for="{{ $methodForm['input_id'] }}">{{ $label }} <span class="text-danger">*</span></label>
            <input
                type="{{ $kind === 'phones' ? 'tel' : 'email' }}"
                class="form-control @error($methodForm['value_field']) is-invalid @enderror"
                id="{{ $methodForm['input_id'] }}"
                name="additional_contacts[{{ $index }}][{{ $kind }}][{{ $methodIndex }}][value]"
                value="{{ $method['value'] ?? '' }}"
                placeholder="{{ $kind === 'phones' ? '(00)00000-0000' : 'E-mail' }}"
                autocomplete="{{ $kind === 'phones' ? 'tel' : 'email' }}"
            >
            @error($methodForm['value_field'])
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col col-sm-4">
            <label class="form-label" for="{{ $methodForm['input_id'] }}-label">Tipo <span class="text-danger">*</span></label>
            <select
                class="form-select @error($methodForm['label_field']) is-invalid @enderror"
                id="{{ $methodForm['input_id'] }}-label"
                name="additional_contacts[{{ $index }}][{{ $kind }}][{{ $methodIndex }}][label]"
                required
            >
                <option value="">Selecione</option>
                @foreach ($methodForm['labels'] as $option)
                    <option value="{{ $option }}" @selected(($method['label'] ?? '') === $option)>{{ $option }}</option>
                @endforeach
            </select>
            @error($methodForm['label_field'])
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-auto">
            <span class="form-label d-block" aria-hidden="true">&nbsp;</span>
            <button type="button" class="btn btn-outline-danger" data-remove-contact-row aria-label="Remover {{ $kind === 'phones' ? 'telefone' : 'e-mail' }}">
                <i class="mdi mdi-delete-outline" aria-hidden="true"></i>
            </button>
        </div>
    </div>
</div>
