<input type="hidden" name="primary_contact[id]" value="{{ $formData['primary_contact']['id'] ?? '' }}">

<div class="row g-3">
    <section class="col-md-6" aria-labelledby="phones-heading">
        <div
            id="phoneInputs"
            data-contact-container
            data-min-rows="1"
            data-next-index="{{ $formData['next_contact_indexes']['phones'] }}"
        >
            @foreach ($formData['primary_contact']['phones'] as $index => $phone)
                <x-form.input-groups.phone
                    :index="$index"
                    :method-id="$phone['id'] ?? null"
                    :value-field="$phone['value_field']"
                    :label-field="$phone['label_field']"
                    :value="$phone['value'] ?? ''"
                    :label="$phone['label'] ?? ''"
                />
            @endforeach
        </div>

        <template id="phoneInputTemplate">
            <x-form.input-groups.phone
                index="__INDEX__"
                :value-field="$formData['primary_contact_templates']['phones']['value_field']"
                :label-field="$formData['primary_contact_templates']['phones']['label_field']"
            />
        </template>

        <div class="d-flex align-items-center mb-2">
            <button
                type="button"
                class="btn btn-sm btn-outline-primary"
                data-add-contact-row
                data-target="#phoneInputs"
                data-template="#phoneInputTemplate"
            >
                Adicionar telefone
            </button>
        </div>
    </section>

    <section class="col-md-6" aria-labelledby="emails-heading">
        <div
            id="emailInputs"
            data-contact-container
            data-min-rows="1"
            data-next-index="{{ $formData['next_contact_indexes']['emails'] }}"
        >
            @foreach ($formData['primary_contact']['emails'] as $index => $email)
                <x-form.input-groups.email
                    :index="$index"
                    :method-id="$email['id'] ?? null"
                    :value-field="$email['value_field']"
                    :label-field="$email['label_field']"
                    :value="$email['value'] ?? ''"
                    :label="$email['label'] ?? ''"
                />
            @endforeach
        </div>

        <template id="emailInputTemplate">
            <x-form.input-groups.email
                index="__INDEX__"
                :value-field="$formData['primary_contact_templates']['emails']['value_field']"
                :label-field="$formData['primary_contact_templates']['emails']['label_field']"
            />
        </template>

        <div class="d-flex align-items-center mb-2">
            <button
                type="button"
                class="btn btn-sm btn-outline-primary"
                data-add-contact-row
                data-target="#emailInputs"
                data-template="#emailInputTemplate"
            >
                Adicionar e-mail
            </button>
        </div>
    </section>
</div>
