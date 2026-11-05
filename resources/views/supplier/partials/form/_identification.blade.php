<div class="d-flex flex-wrap align-items-center gap-3 mb-3">
    <div class="form-check d-flex align-items-center gap-2 p-0 m-0">
        <input
            class="form-check-input flex-shrink-0 m-0"
            type="radio"
            name="type"
            id="type-company"
            value="company"
            @checked($formData['type'] === 'company')
        >
        <label class="form-check-label" for="type-company">Pessoa Jurídica</label>
    </div>

    <div class="form-check d-flex align-items-center gap-2 p-0 m-0">
        <input
            class="form-check-input flex-shrink-0 m-0"
            type="radio"
            name="type"
            id="type-individual"
            value="individual"
            @checked($formData['type'] === 'individual')
        >
        <label class="form-check-label" for="type-individual">Pessoa Física</label>
    </div>
</div>

{{-- inputs pessoa juridica --}}
<div class="row js-company-fields {{ $formData['type'] === 'company' ? '' : 'd-none' }}">
    <div class="col-md-3 mb-3">
        <label for="cnpj" class="form-label">CNPJ <span class="text-danger">*</span></label>
        <input
            class="form-control @error('cnpj') is-invalid @enderror"
            type="text"
            id="cnpj"
            name="cnpj"
            value="{{ old('cnpj', $formData['cnpj']) }}"
            data-toggle="input-mask"
            data-mask-format="00.000.000/0000-00"
            data-lookup-url="{{ route('suppliers.lookup-cnpj', ['cnpj' => '00000000000000']) }}"
            placeholder="00.000.000/0000-00"
            required
        >
        <div id="cnpj-feedback" role="status" aria-live="polite"></div>
        @error('cnpj')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3 mb-3">
        <label for="corporate_name" class="form-label">Razão social <span class="text-danger">*</span></label>
        <input
            class="form-control @error('corporate_name') is-invalid @enderror"
            type="text"
            id="corporate_name"
            name="corporate_name"
            value="{{ old('corporate_name', $formData['corporate_name']) }}"
            required
        >
         @error('corporate_name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3 mb-3">
        <label for="trade_name" class="form-label">Nome Fantasia <span class="text-danger">*</span></label>
        <input
            class="form-control @error('trade_name') is-invalid @enderror"
            type="text"
            id="trade_name"
            name="trade_name"
            value="{{ old('trade_name', $formData['trade_name']) }}"
            required
        >
         @error('trade_name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3 mb-3">
        <label for="state_registration_indicator" class="form-label">Indicador de Inscrição Estadual <span class="text-danger">*</span></label>
        <select class="form-select @error('state_registration_indicator') is-invalid @enderror" name="state_registration_indicator" required>
            <option value="">Selecione</option>
            <option
                value="contribuinte"
                @selected(old('state_registration_indicator', $formData['state_registration_indicator']) == 'contribuinte')
            >
            Contribuinte
            </option>
            <option
                value="isento"
                @selected(old('state_registration_indicator', $formData['state_registration_indicator']) == 'isento')
            >
            Isento
            </option>
            <option
                value="nao_contribuinte"
                @selected(old('state_registration_indicator', $formData['state_registration_indicator']) == 'nao_contribuinte')
            >
            Não contribuente
            </option>
        </select>
        @error('state_registration_indicator')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3 mb-3">
        <label for="state_registration" class="form-label">Inscrição Estadual</label>
        <input
            class="form-control"
            type="text"
            id="state_registration"
            name="state_registration"
            value="{{ old('state_registration', $formData['state_registration']) }}"
            maxlength="32"
        >
    </div>

    <div class="col-md-3 mb-3">
        <label for="municipal_registration" class="form-label">Inscrição Municipal</label>
        <input
            class="form-control"
            type="text"
            id="municipal_registration"
            name="municipal_registration"
            value="{{ old('municipal_registration', $formData['municipal_registration']) }}"
            maxlength="32"
        >
    </div>

    <div class="col-md-3 mb-3">
        <label for="cnpj_status" class="form-label">Situação CNPJ</label>
        <input
            class="form-control"
            type="text"
            id="cnpj_status"
            name="cnpj_status"
            value="{{ old('cnpj_status', $formData['cnpj_status']) }}"
            readonly
            required
        >
    </div>

    <div class="col-md-3 mb-3">
        <label for="tax_regime" class="form-label">Recolhimento</label>
        <select class="form-select @error('tax_regime') is-invalid @enderror" name="tax_regime">
            <option value="">Selecione</option>
            <option
                value="withheld_by_recipient"
                @selected(old('tax_regime', $formData['tax_regime']) == 'withheld_by_recipient')
            >
                Retido pelo tomador
            </option>
            <option
                value="payable_by_provider"
                @selected(old('tax_regime', $formData['tax_regime']) == 'payable_by_provider')
            >
                A recolher pelo prestador
            </option>
        </select>
        @error('tax_regime')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

</div>

{{-- inputs pessoa fisica --}}
<div class="row js-individual-fields {{ $formData['type'] === 'individual' ? '' : 'd-none' }}">
    <div class="col-md-3 mb-3">
        <label for="cpf" class="form-label">CPF <span class="text-danger">*</span></label>
        <input
            class="form-control @error('cpf') is-invalid @enderror"
            type="text"
            id="cpf"
            name="cpf"
            value="{{ old('cpf', $formData['cpf']) }}"
            placeholder="000.000.000-00"
            data-toggle="input-mask"
            data-mask-format="000.000.000-00"
            required
        >
        @error('cpf')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3 mb-3">
        <label for="name" class="form-label">Nome <span class="text-danger">*</span></label>
        <input
            class="form-control @error('name') is-invalid @enderror"
            type="text"
            id="name"
            name="name"
            value="{{ old('name', $formData['name']) }}"
            required
        >
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3 mb-3">
        <label for="nickname" class="form-label">Apelido</label>
        <input
            class="form-control"
            type="text"
            id="nickname"
            name="nickname"
            value="{{ old('nickname', $formData['nickname']) }}"
        >
    </div>

    <div class="col-md-3 mb-3">
        <label for="rg" class="form-label">RG <span class="text-danger">*</span></label>
        <input
            class="form-control @error('rg') is-invalid @enderror"
            type="text"
            id="rg"
            name="rg"
            value="{{ old('rg', $formData['rg']) }}"
            required
        >
        @error('rg')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

{{-- inputs em comum --}}
<div class="row">
    <div class="col-md-3 mb-3">
        <label for="active" class="form-label">Ativo</label>
        <select class="form-select" name="active">
            <option
                value="1"
                @selected(old('active', $formData['active']) == 1)
            >
                Sim
            </option>
            <option
                value="0"
                @selected(old('active', $formData['active']) == 0)
            >
                Não
            </option>
        </select>
    </div>
</div>
