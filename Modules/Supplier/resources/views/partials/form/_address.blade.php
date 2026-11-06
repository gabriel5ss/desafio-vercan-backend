@php
    $selectedState = old('state_id', $supplier->state_id ?? '');
    $selectedCity = old('city_id', $supplier->city_id ?? '');
    $condominium = old('condominium', $supplier->condominium ?? '');
    $condominium = is_bool($condominium) ? (int) $condominium : $condominium;
    $isCondominium = (string) $condominium === '1';
@endphp

<div class="row g-3">
    <div class="col-md-6 col-xl-3">
        <label for="postal_code" class="form-label">CEP <span class="text-danger">*</span></label>
        <input
            class="form-control @error('postal_code') is-invalid @enderror"
            type="text"
            id="postal_code"
            name="postal_code"
            value="{{ old('postal_code', $supplier->postal_code ?? '') }}"
            maxlength="9"
            placeholder="00000-000"
            inputmode="numeric"
            autocomplete="postal-code"
            aria-describedby="postal-code-feedback"
            data-toggle="input-mask"
            data-mask-format="00000-000"
            required
        >
        <div id="postal-code-feedback" class="form-text" role="status" aria-live="polite"></div>
        @error('postal_code')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 col-xl-3">
        <label for="street" class="form-label">Logradouro <span class="text-danger">*</span></label>
        <input
            class="form-control @error('street') is-invalid @enderror"
            type="text"
            id="street"
            name="street"
            value="{{ old('street', $supplier->street ?? '') }}"
            maxlength="255"
            required
        >
        @error('street')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 col-xl-3">
        <label for="number" class="form-label">Número <span class="text-danger">*</span></label>
        <input
            class="form-control @error('number') is-invalid @enderror"
            type="text"
            id="number"
            name="number"
            value="{{ old('number', $supplier->number ?? '') }}"
            maxlength="30"
            required
        >
        @error('number')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 col-xl-3">
        <label for="complement" class="form-label">Complemento</label>
        <input
            class="form-control @error('complement') is-invalid @enderror"
            type="text"
            id="complement"
            name="complement"
            value="{{ old('complement', $supplier->complement ?? '') }}"
            maxlength="255"
        >
        @error('complement')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 col-xl-3">
        <label for="district" class="form-label">Bairro <span class="text-danger">*</span></label>
        <input
            class="form-control @error('district') is-invalid @enderror"
            type="text"
            id="district"
            name="district"
            value="{{ old('district', $supplier->district ?? '') }}"
            maxlength="120"
            required
        >
        @error('district')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 col-xl-3">
        <label for="reference" class="form-label">Ponto de Referência</label>
        <input
            class="form-control @error('reference') is-invalid @enderror"
            type="text"
            id="reference"
            name="reference"
            value="{{ old('reference', $supplier->reference ?? '') }}"
            maxlength="255"
        >
        @error('reference')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 col-xl-3">
        <label for="state_id" class="form-label">UF <span class="text-danger">*</span></label>
        <select class="form-select @error('state_id') is-invalid @enderror" id="state_id" name="state_id" required>
            <option value="">Selecione</option>
            @foreach ($states as $state)
                <option value="{{ $state->id }}" @selected((string) $selectedState === (string) $state->id)>
                    {{ $state->code }}
                </option>
            @endforeach
        </select>
        @error('state_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 col-xl-3">
        <label for="city_id" class="form-label">Cidade <span class="text-danger">*</span></label>
        <select class="form-select @error('city_id') is-invalid @enderror" id="city_id" name="city_id" required @disabled($selectedState === '')>
            <option value="">Selecione</option>
            @foreach ($cities as $city)
                @if ((string) $selectedState === (string) $city->state_id)
                    <option value="{{ $city->id }}" @selected((string) $selectedCity === (string) $city->id)>
                        {{ $city->name }}
                    </option>
                @endif
            @endforeach
        </select>
        <template id="supplier-city-options">
            @foreach ($cities as $city)
                <option value="{{ $city->id }}" data-state-id="{{ $city->state_id }}">{{ $city->name }}</option>
            @endforeach
        </template>
        @error('city_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 col-xl-3">
        <label for="condominium" class="form-label">Condomínio? <span class="text-danger">*</span></label>
        <select class="form-select @error('condominium') is-invalid @enderror" id="condominium" name="condominium" required>
            <option value="" @selected((string) $condominium === '')>Selecione</option>
            <option value="1" @selected((string) $condominium === '1')>Sim</option>
            <option value="0" @selected((string) $condominium === '0')>Não</option>
        </select>
        @error('condominium')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row g-3 mt-0 js-condominium-fields {{ $isCondominium ? '' : 'd-none' }}">
    <div class="col-md-6 col-xl-3">
        <label for="condominium_address" class="form-label">Endereço no condomínio <span class="text-danger">*</span></label>
        <input
            class="form-control @error('condominium_address') is-invalid @enderror"
            type="text"
            id="condominium_address"
            name="condominium_address"
            value="{{ old('condominium_address', $supplier->condominium_address ?? '') }}"
            maxlength="255"
            @required($isCondominium)
            @disabled(! $isCondominium)
        >
        @error('condominium_address')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6 col-xl-3">
        <label for="condominium_number" class="form-label">Número no condomínio <span class="text-danger">*</span></label>
        <input
            class="form-control @error('condominium_number') is-invalid @enderror"
            type="text"
            id="condominium_number"
            name="condominium_number"
            value="{{ old('condominium_number', $supplier->condominium_number ?? '') }}"
            maxlength="30"
            @required($isCondominium)
            @disabled(! $isCondominium)
        >
        @error('condominium_number')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
