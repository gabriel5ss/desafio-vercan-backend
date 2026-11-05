$(function () {
    const $form = $('#supplier-form'); // pegar o form pelo id

    function togglePersonFields() {
        const type = $form.find('input[name="type"]:checked').val();
        const isCompany = type === 'company';

        console.log('chamou')

        $form.find('.js-company-fields') // procurar o objeto da empresa pela classe
            .toggleClass('d-none', !isCompany) // alternar a classe quando não for company
            .find(':input') // localizar o input
            .prop('disabled', !isCompany); // colocar disabled quando não for company para não enviar

        $form.find('.js-individual-fields') // procurar objetos da pessoa fisica pela classe
            .toggleClass('d-none', isCompany) // ocultar quando for empresa
            .find(':input') // pegar o input
            .prop('disabled', isCompany); // colocar disabled quando for empresa para não enviar no post
    }

    $form.on('change', 'input[name="type"]', togglePersonFields); // quando o radio for alternado a função é invocada

    togglePersonFields();

    function updateContactRemoveButtons($container) {
        const minimumRows = Number($container.attr('data-min-rows') || 0);
        const rowCount = $container.find('[data-contact-row]').length;

        $container.find('[data-remove-contact-row]').prop('disabled', rowCount <= minimumRows);
    }

    $form.on('click', '[data-add-contact-row]', function () {
        const $button = $(this);
        const $container = $($button.data('target'));
        const $template = $($button.data('template'));

        if (!$container.length || !$template.length) {
            return;
        }

        const index = Number($container.attr('data-next-index')); // pegar o valor do proximo index
        const row = $template.html().replace(/__INDEX__/g, String(index)); // trocar o placeholder do componente

        $container.append(row);
        $container.attr('data-next-index', String(index + 1)); // incrementar o numero do index para o proximo componente

        updateContactRemoveButtons($container);
        $form.trigger('supplier:contacts-updated');
    });

    $form.on('click', '[data-remove-contact-row]', function () {
        const $container = $(this).closest('[data-contact-container]');
        const minimumRows = Number($container.attr('data-min-rows') || 0);

        // verificar se é o componente 0 - o obrigatorio
        // não remover
        if ($container.find('[data-contact-row]').length <= minimumRows) {
            return;
        }

        $(this).closest('[data-contact-row]').remove(); // remover o componente
        updateContactRemoveButtons($container);
    });

    function renumberAdditionalContacts() {
        const $contacts = $form.find('[data-additional-contact]');

        $form.find('#additionalContactsEmpty').toggleClass('d-none', $contacts.length > 0);
        $contacts.each(function (index) {
            $(this).find('[data-additional-contact-title]').text(`Contato ${index + 1}`);
        });
    }

    $form.on('click', '[data-add-additional-contact]', function () {
        const $container = $form.find('#additionalContacts');
        const $template = $form.find('#additionalContactTemplate');
        const index = Number($container.attr('data-next-index'));

        $container.append($template.html().replace(/__CONTACT_INDEX__/g, String(index)));
        $container.attr('data-next-index', String(index + 1));
        renumberAdditionalContacts();
        $form.trigger('supplier:contacts-updated');
        $container.children().last().find('input').first().trigger('focus');
    });

    $form.on('click', '[data-remove-additional-contact]', function () {
        $(this).closest('[data-additional-contact]').remove();
        renumberAdditionalContacts();
    });

    $form.find('[data-contact-container]').each(function () {
        updateContactRemoveButtons($(this));
    });
});

$(function () {
    function phoneMask(value) {
        const digits = value.replace(/\D/g, '');

        return digits.length > 10
            ? '(00)00000-0000'
            : '(00)0000-00009';
    }

    const phoneOptions = {
        onKeyPress(value, event, field, options) {
            field.mask(phoneMask(value), options);
        },
    };

    function applyPhoneMasks() {
        $('input[type="tel"]').mask(phoneMask, phoneOptions);
    }

    $('#supplier-form').on('supplier:contacts-updated', applyPhoneMasks);
    applyPhoneMasks();
})

$(function () {
    const $form = $('#supplier-form');
    const $cnpj = $form.find('[name="cnpj"]');
    const $feedback = $form.find('#cnpj-feedback');

    let timer;
    let request;

    function currentCnpj() {
        return String($cnpj.val() || '').replace(/\D/g, '');
    }

    function isCompany() {
        return $form.find('[name="type"]:checked').val() === 'company';
    }

    function normalizeLocation(value) {
        return String(value ?? '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .trim()
            .toUpperCase();
    }

    function cancelLookup() {
        clearTimeout(timer);
        request?.abort();
        $feedback.text('');
    }

    $cnpj.on('input', function () {
        cancelLookup();

        const cnpj = currentCnpj();

        if (cnpj.length !== 14 || !isCompany()) {
            return;
        }

        timer = setTimeout(function () {
            $feedback.text('Consultando CNPJ...');

            request = $.ajax({
                url: $cnpj.attr('data-lookup-url')
                    .replace('00000000000000', cnpj),
                method: 'GET',
                dataType: 'json',
                headers: { Accept: 'application/json' },
                timeout: 15000,
            })
                .done(function ({ data }) {
                    if (currentCnpj() !== cnpj || !isCompany()) {
                        return;
                    }

                    $.each(['corporate_name', 'trade_name', 'cnpj_status', 'street', 'number', 'complement', 'district'], function (index, field) {
                        $form.find(`[name="${field}"]`)
                            .val(data[field] ?? '')
                            .trigger('change');
                    });

                    const postalCode = String(data.postal_code ?? '').replace(/\D/g, '');
                    $form.find('[name="postal_code"]')
                        .val(postalCode.replace(/^(\d{5})(\d{3})$/, '$1-$2'))
                        .trigger('change');

                    const $state = $form.find('[name="state_id"]');
                    const stateId = $state.find('option').filter(function () {
                        return $(this).val() !== ''
                            && normalizeLocation($(this).text()) === normalizeLocation(data.state);
                    }).first().val();

                    $state.val(stateId ?? '').trigger('change');

                    const $city = $form.find('[name="city_id"]');
                    const cityId = $city.find('option').filter(function () {
                        return $(this).val() !== ''
                            && normalizeLocation($(this).text()) === normalizeLocation(data.city);
                    }).first().val();

                    $city.val(cityId ?? '').trigger('change');

                    $feedback.text('Dados preenchidos.');
                })
                .fail(function (xhr, status) {
                    if (status === 'abort') {
                        return;
                    }

                    $feedback.text(
                        xhr.responseJSON?.errors?.cnpj?.[0]
                        ?? xhr.responseJSON?.message
                        ?? 'Não foi possível consultar o CNPJ.'
                    );
                });
        }, 400);
    });

    $form.on('change', '[name="type"]', cancelLookup);
});

$(function () {
    const $form = $('#supplier-form');
    const $state = $form.find('#state_id');
    const $city = $form.find('#city_id');
    const $cityTemplate = $form.find('#supplier-city-options');

    function updateCities() {
        const stateId = String($state.val() || '');
        const selectedCity = $city.val();

        if (!$cityTemplate.length) {
            return;
        }

        $city.empty().append($('<option>', { value: '', text: 'Selecione' }));

        if (stateId) {
            $city.append(
                $cityTemplate.contents().filter('option').filter(function () {
                    return $(this).attr('data-state-id') === stateId;
                }).clone()
            );
        }

        $city.val(selectedCity || '');
        if ($city.val() === null) {
            $city.val('');
        }
        $city.prop('disabled', !stateId);
    }

    function toggleCondominiumFields() {
        const isCondominium = $form.find('#condominium').val() === '1';

        $form.find('.js-condominium-fields')
            .toggleClass('d-none', !isCondominium)
            .find(':input')
            .prop('disabled', !isCondominium)
            .prop('required', isCondominium);
    }

    $state.on('change', updateCities);
    $form.on('change', '#condominium', toggleCondominiumFields);

    updateCities();
    toggleCondominiumFields();

    const $postalCode = $form.find('#postal_code');
    const $postalCodeFeedback = $form.find('#postal-code-feedback');
    let postalCodeTimer;
    let postalCodeRequest;

    function currentPostalCode() {
        return String($postalCode.val() || '').replace(/\D/g, '');
    }

    function normalizeAddressLocation(value) {
        return String(value ?? '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .trim()
            .toUpperCase();
    }

    $postalCode.on('input', function () {
        clearTimeout(postalCodeTimer);
        postalCodeRequest?.abort();
        $postalCodeFeedback.text('');

        const postalCode = currentPostalCode();

        if (!/^\d{8}$/.test(postalCode)) {
            return;
        }

        postalCodeTimer = setTimeout(function () {
            if (currentPostalCode() !== postalCode) {
                return;
            }

            $postalCodeFeedback.text('Consultando CEP...');

            postalCodeRequest = $.ajax({
                url: `https://viacep.com.br/ws/${postalCode}/json/`,
                method: 'GET',
                dataType: 'json',
                timeout: 10000,
            })
                .done(function (data) {
                    if (currentPostalCode() !== postalCode) {
                        return;
                    }

                    if (data.erro) {
                        $postalCodeFeedback.text('CEP não encontrado. Preencha o endereço manualmente.');
                        return;
                    }

                    $.each({ street: data.logradouro, district: data.bairro, complement: data.complemento }, function (field, value) {
                        $form.find(`[name="${field}"]`).val(value ?? '').trigger('change');
                    });

                    const stateId = $state.find('option').filter(function () {
                        return $(this).val() !== ''
                            && normalizeAddressLocation($(this).text()) === normalizeAddressLocation(data.uf);
                    }).first().val();

                    $state.val(stateId ?? '').trigger('change');

                    const cityId = $city.find('option').filter(function () {
                        return $(this).val() !== ''
                            && normalizeAddressLocation($(this).text()) === normalizeAddressLocation(data.localidade);
                    }).first().val();

                    $city.val(cityId ?? '').trigger('change');
                    $postalCodeFeedback.text('Endereço preenchido. Confira os dados e informe o número.');
                })
                .fail(function (xhr, status) {
                    if (status === 'abort' || currentPostalCode() !== postalCode) {
                        return;
                    }

                    $postalCodeFeedback.text('Não foi possível consultar o CEP. Preencha o endereço manualmente.');
                });
        }, 400);
    });
});
