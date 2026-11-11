import ptBR from 'datatables.net-plugins/i18n/pt-BR.mjs';
import { formatEvent, formatEntity, renderChanges } from './audit-formatters.js';

$(function () {
    function renderGroup(audits, type, row) {
        if (type !== 'display') {
            return row.group_id;
        }

        const $group = $('<details>').append(
            $('<summary>').text(`${audits.length} registro(s) — Ver alterações`),
        );
        audits.forEach((audit) => {
            const change = audit.changes;
            $group.append($('<div>', { class: 'mt-2 mb-3' }).append(
                $('<p>', { class: 'fw-semibold mb-2' }).text(
                    `${formatEvent(audit.event)} · ${formatEntity(change.type)} #${change.id}`,
                ),
                renderChanges(change.old_values, change.new_values),
            ));
        });

        return $group.prop('outerHTML');
    }

    $('[data-audit-history-table]').each(function () {
        const $table = $(this);
        const $error = $table.closest('[data-audit-history]').find('[data-audit-history-error]');

        $table.DataTable({
            processing: true,
            serverSide: true,
            pageLength: 20,
            lengthChange: true,
            lengthMenu: [5, 10, 20, 50, 100],
            searching: false,
            ordering: false,
            language: ptBR,
            ajax: function (data, callback) {
                $error.addClass('d-none');
                $.ajax({
                    url: $table.attr('data-source'),
                    headers: { Accept: 'application/json' },
                    data,
                }).done(callback).fail(() => {
                    $error.removeClass('d-none');
                    callback({ draw: data.draw, recordsTotal: 0, recordsFiltered: 0, data: [] });
                });
            },
            columns: [
                { data: 'created_at', defaultContent: '—', render: $.fn.dataTable.render.text() },
                { data: 'user_name', defaultContent: 'Sistema', render: $.fn.dataTable.render.text() },
                { data: 'audit', render: renderGroup },
            ],
        });
    });
});
