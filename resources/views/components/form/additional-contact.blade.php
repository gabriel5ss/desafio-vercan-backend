@props(['index', 'contact', 'number' => 1])

<div {{ $attributes->merge(['class' => 'border rounded bg-light']) }} data-additional-contact>
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 p-3 border-bottom">
        <h5 class="m-0" data-additional-contact-title>Contato {{ $number }}</h5>
        <div class="d-flex align-items-center gap-3">
            <button
                type="button"
                class="card-collapse-toggle"
                data-bs-toggle="collapse"
                data-bs-target="#additional-contact-{{ $index }}"
                aria-expanded="true"
                aria-controls="additional-contact-{{ $index }}"
                aria-label="Alternar contato"
            >
                <i class="mdi mdi-chevron-down card-collapse-arrow" aria-hidden="true"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger" data-remove-additional-contact>
                <i class="mdi mdi-delete-outline" aria-hidden="true"></i> Remover
            </button>
        </div>
    </div>

    <div id="additional-contact-{{ $index }}" class="collapse show">
        <div class="p-3">
            <div class="row g-3 mb-3">
                @foreach ($contact['fields'] as $field => $input)
                    <div class="col-md-4">
                        <label class="form-label" for="additional-{{ $index }}-{{ $field }}">
                            {{ $input['label'] }} @if ($input['required']) <span class="text-danger">*</span> @endif
                        </label>
                        <input
                            type="text"
                            class="form-control @error($input['error_key']) is-invalid @enderror"
                            id="additional-{{ $index }}-{{ $field }}"
                            name="additional_contacts[{{ $index }}][{{ $field }}]"
                            value="{{ $input['value'] }}"
                            maxlength="{{ $input['maxlength'] }}"
                            @required($input['required'])
                        >
                        @error($input['error_key'])
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @endforeach
            </div>

            <div class="row g-3">
                @foreach ($contact['method_groups'] as $group)
                    <section class="col-xl-6 d-flex flex-column align-items-start" aria-label="{{ $group['label'] }} do contato">
                        <div id="{{ $group['container_id'] }}" class="w-100 flex-grow-1" data-contact-container data-next-index="{{ $group['next_index'] }}">
                            @foreach ($group['rows'] as $methodData)
                                @include('supplier.partials.form._additional_contact_method', $methodData)
                            @endforeach
                        </div>
                        <template id="{{ $group['container_id'] }}-template">
                            @include('supplier.partials.form._additional_contact_method', $group['template'])
                        </template>
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-primary"
                            data-add-contact-row
                            data-target="#{{ $group['container_id'] }}"
                            data-template="#{{ $group['container_id'] }}-template"
                        >
                            <i class="mdi mdi-plus" aria-hidden="true"></i>
                            Adicionar {{ $group['button_label'] }}
                        </button>
                    </section>
                @endforeach
            </div>
        </div>
    </div>
</div>
