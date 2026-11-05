<p id="additionalContactsEmpty" class="text-muted mb-0 {{ $formData['additional_contacts'] === [] ? '' : 'd-none' }}">
    Não há contatos adicionais.
</p>

<div id="additionalContacts" class="d-flex flex-column gap-3" data-next-index="{{ $formData['next_additional_contact_index'] }}">
    @foreach ($formData['additional_contacts'] as $index => $contact)
        <x-form.additional-contact :index="$index" :contact="$contact" :number="$loop->iteration" />
    @endforeach
</div>

<template id="additionalContactTemplate">
    <x-form.additional-contact index="__CONTACT_INDEX__" :contact="$formData['additional_contact_template']" />
</template>
