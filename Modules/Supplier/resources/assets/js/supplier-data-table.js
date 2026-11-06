import ptBR from 'datatables.net-plugins/i18n/pt-BR.mjs';
import enGB from 'datatables.net-plugins/i18n/en-GB.mjs';
import esES from 'datatables.net-plugins/i18n/es-ES.mjs';

$(function () {

    const translations = {
        pt: ptBR,
        'pt-br': ptBR,
        en: enGB,
        'en-gb': enGB,
        es: esES,
        'es-es': esES,
    };

    const $table = $('#suppliers-table');

    if (!$table.length) {
        return;
    }

    const locale = ($table.attr('data-locale') ?? 'en')
    .replaceAll('_', '-')
    .toLowerCase();

    const language = translations[locale]
    ?? translations[locale.split('-')[0]]
    ?? enGB;

    const textRenderer = $.fn.dataTable.render.text();

    $table.DataTable({
        processing: true,
        serverSide: true,
        pageLength: 15,
        lengthMenu: [15, 25, 50, 100],
        order: [[0, 'asc']],
        orderMulti: false,
        searchDelay: 400,
        language: language,
        ajax: {
            url: $table.attr('data-source'),
            headers: {
                Accept: 'application/json',
            },
        },

        columns: [
            {
                data: 'name',
                name: 'name',
                render: textRenderer,
            },
            {
                data: 'trade_name',
                name: 'trade_name',
                render: textRenderer,
            },
            {
                data: 'document',
                name: 'document',
                render: textRenderer,
            },
            {
                data: 'active',
                name: 'active',
                render: function (value, type) {
                    return type === 'display'
                        ? (value ? 'Sim' : 'Não')
                        : value;
                },
            },
            {
                data: 'edit_url',
                name: '',
                orderable: false,
                searchable: false,
                render: function (url, type, row) {
                    if (type !== 'display') {
                        return url;
                    }

                    const $showButton = $('<a>', {
                        href: row.show_url,
                        class: 'btn btn-sm btn-outline-info',
                        title: 'Ver fornecedor',
                        'aria-label': 'Ver fornecedor',
                    }).append($('<i>', {
                        class: 'mdi mdi-eye-outline',
                        'aria-hidden': 'true',
                    }));

                    const $editButton = $('<a>', {
                        href: url,
                        class: 'btn btn-sm btn-outline-primary',
                        title: 'Editar fornecedor',
                        'aria-label': 'Editar fornecedor',
                    }).append($('<i>', {
                        class: 'mdi mdi-pencil-outline',
                        'aria-hidden': 'true',
                    }));

                    const $deleteForm = $('<form>', {
                        action: row.delete_url,
                        method: 'POST',
                        'data-delete-supplier': '',
                    }).append(
                        $('<input>', {
                            type: 'hidden',
                            name: '_token',
                            value: $table.attr('data-csrf-token'),
                        }),
                        $('<input>', {
                            type: 'hidden',
                            name: '_method',
                            value: 'DELETE',
                        }),
                        $('<button>', {
                            type: 'submit',
                            class: 'btn btn-sm btn-outline-danger',
                            title: 'Excluir fornecedor',
                            'aria-label': 'Excluir fornecedor',
                        }).append($('<i>', {
                            class: 'mdi mdi-delete-outline',
                            'aria-hidden': 'true',
                        }))
                    );

                    return $('<div>', {
                        class: 'd-flex align-items-center gap-2',
                    })
                        .append($showButton, $editButton, $deleteForm)
                        .prop('outerHTML');
                },
            },
        ],
    });

    $table.on('submit', '[data-delete-supplier]', function (event) {
        if (!confirm('Deseja excluir este fornecedor?')) {
            event.preventDefault();
        }
    });
});
