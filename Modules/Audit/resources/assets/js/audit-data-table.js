import { formatEvent, formatEntity, renderValues } from './audit-formatters.js';
import ptBR from 'datatables.net-plugins/i18n/pt-BR.mjs';
import enGB from 'datatables.net-plugins/i18n/en-GB.mjs';
import esES from 'datatables.net-plugins/i18n/es-ES.mjs';

$(function () {
    const $table = $('#audits-table');

    if (!$table.length) {
        return;
    }

    const translations = {
        pt: ptBR,
        'pt-br': ptBR,
        en: enGB,
        'en-gb': enGB,
        es: esES,
        'es-es': esES,
    };
    const locale = ($table.attr('data-locale') ?? 'en').replaceAll('_', '-').toLowerCase();
    const textRenderer = $.fn.dataTable.render.text();

    $table.DataTable({
        processing: true,
        serverSide: true,
        pageLength: 15,
        lengthMenu: [15, 25, 50, 100],
        order: [[0, 'desc']],
        orderMulti: false,
        searchDelay: 400,
        language: translations[locale] ?? translations[locale.split('-')[0]] ?? enGB,
        ajax: {
            url: $table.attr('data-source'),
            headers: { Accept: 'application/json' },
        },
        columns: [...[
            'created_at',
            'user_name',
            'event',
            'auditable_type',
            'auditable_id',
            'ip_address',
        ].map((field) => ({
            data: field,
            name: field,
            defaultContent: '—',
            render: function (value, type) {
                const label = field === 'event' ? formatEvent(value)
                    : field === 'auditable_type' ? formatEntity(value)
                    : value;

                return type === 'display' ? textRenderer.display(label ?? '—') : value;
            },
        })), ...['old_values', 'new_values'].map((field) => ({
            data: field,
            name: field,
            orderable: false,
            searchable: false,
            render: function (value, type) {
                return type === 'display' ? renderValues(value) : JSON.stringify(value ?? {});
            },
        }))],
    });
});
