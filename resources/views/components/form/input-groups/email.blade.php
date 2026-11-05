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
            <label class="form-label" for="email-{{ $index }}">
                E-mail <span class="text-danger">*</span>
            </label>
            <input
                class="form-control @error($valueField) is-invalid @enderror"
                type="email"
                id="email-{{ $index }}"
                name="primary_contact[emails][{{ $index }}][value]"
                value="{{ old($valueField, $value) }}"
                placeholder="E-mail"
                autocomplete="email"
                required
            >
            @error($valueField)
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-5">
            <label class="form-label" for="email-label-{{ $index }}">
                Tipo <span class="text-danger">*</span>
            </label>
            <select
                class="form-select @error($valueField) is-invalid @enderror"
                id="email-label-{{ $index }}"
                name="primary_contact[emails][{{ $index }}][label]"
                aria-label="Tipo de e-mail"
            >
                <option value="">Selecione</option>
                <option value="Pessoal" @selected(old($labelField, $label) === 'Pessoal')>Pessoal</option>
                <option value="Comercial" @selected(old($labelField, $label) === 'Comercial')>Comercial</option>
                <option value="Financeiro" @selected(old($labelField, $label) === 'Financeiro')>Financeiro</option>
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
                aria-label="Remover e-mail"
                @disabled($index == 0)
            >
                <i class="mdi mdi-delete-outline" aria-hidden="true"></i>
            </button>
        </div>
    </div>
</div>
