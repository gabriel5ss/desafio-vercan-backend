@props([
    'index' => '__INDEX__',
    'valueField',
    'labelField',
    'value' => '',
    'label' => '',
])

<div class="mb-3" data-contact-row>
    <div class="row gx-2 align-items-end flex-nowrap">
        <div class="col-md-5">
            <label class="form-label" for="phone-{{ $index }}">
                Telefone <span class="text-danger">*</span>
            </label>
            <input
                class="form-control @error($valueField) is-invalid @enderror"
                type="tel"
                id="phone-{{ $index }}"
                name="primary_contact[phones][{{ $index }}][value]"
                value="{{ old($valueField, $value) }}"
                placeholder="(00)00000-0000"
                autocomplete="tel"
                data-toggle="phoneMask"
                required
            >
            @error($valueField)
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-5">
            <label class="form-label" for="phone-label-{{ $index }}">
                Tipo <span class="text-danger">*</span>
            </label>
            <select
                class="form-select @error($valueField) is-invalid @enderror"
                id="phone-label-{{ $index }}"
                name="primary_contact[phones][{{ $index }}][label]"
                aria-label="Tipo de telefone"
                required
            >
                <option value="">Selecione</option>
                <option value="Celular" @selected(old($labelField, $label) === 'Celular')>Celular</option>
                <option value="Comercial" @selected(old($labelField, $label) === 'Comercial')>Comercial</option>
                <option value="Residencial" @selected(old($labelField, $label) === 'Residencial')>Residencial</option>
                <option value="Outro" @selected(old($labelField, $label) === 'Outro')>Outro</option>
            </select>
            @error($valueField)
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-auto">
            <button
                type="button"
                class="btn btn-outline-danger {{ $index == 0 ? 'invisible' : '' }}"
                data-remove-contact-row
                aria-label="Remover telefone"
                @disabled($index == 0)
            >
                <i class="mdi mdi-delete-outline" aria-hidden="true"></i>
            </button>
        </div>
    </div>
</div>
