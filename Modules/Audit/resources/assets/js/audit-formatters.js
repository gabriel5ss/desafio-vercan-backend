const events = {
    created: 'Cadastro', updated: 'Atualização', deleted: 'Exclusão', restored: 'Restauração',
};
const entities = {
    User: 'Usuário', users: 'Usuário', UserObservation: 'Observação do usuário',
    Supplier: 'Fornecedor', supplier: 'Fornecedor', Company: 'Pessoa jurídica', company: 'Pessoa jurídica',
    Individual: 'Pessoa física', individual: 'Pessoa física', Contact: 'Contato', contact: 'Contato',
    ContactMethod: 'Meio de contato', contact_method: 'Meio de contato',
};
const fields = {
    id: 'Código', name: 'Nome', email: 'E-mail', content: 'Observação', observation: 'Observação',
    corporate_name: 'Razão social', trade_name: 'Nome fantasia', nickname: 'Apelido',
    cpf: 'CPF', cnpj: 'CNPJ', rg: 'RG', active: 'Ativo',
    state_registration_indicator: 'Indicador de inscrição estadual', state_registration: 'Inscrição estadual',
    municipal_registration: 'Inscrição municipal', tax_regime: 'Recolhimento', cnpj_status: 'Situação do CNPJ',
    postal_code: 'CEP', street: 'Logradouro', number: 'Número', complement: 'Complemento',
    district: 'Bairro', reference: 'Referência', state: 'Estado', city: 'Cidade',
    state_id: 'Código do estado', city_id: 'Código da cidade', condominium: 'Condomínio',
    condominium_address: 'Endereço do condomínio', condominium_number: 'Número do condomínio',
    is_primary: 'Contato principal', company: 'Empresa', position: 'Cargo', kind: 'Tipo',
    value: 'Valor', label: 'Descrição', user_id: 'Código do usuário', supplier_id: 'Código do fornecedor',
    contact_id: 'Código do contato', supplierable_type: 'Tipo de pessoa', supplierable_id: 'Código da pessoa',
    created_at: 'Data de cadastro', updated_at: 'Data de atualização', deleted_at: 'Data de exclusão',
    email_verified_at: 'Data de verificação do e-mail',
};
const values = {
    kind: { email: 'E-mail', phone: 'Telefone', whatsapp: 'WhatsApp' },
    state_registration_indicator: {
        contribuinte: 'Contribuinte', isento: 'Isento', nao_contribuinte: 'Não contribuinte',
    },
    tax_regime: {
        withheld_by_recipient: 'Retido pelo tomador', payable_by_provider: 'A recolher pelo prestador',
    },
    supplierable_type: entities,
};

function formatValue(value, field) {
    if (value === null || value === undefined || value === '') {
        return '—';
    }
    if (typeof value === 'boolean' || ['active', 'condominium', 'is_primary'].includes(field)) {
        return [true, 1, '1', 'true'].includes(value) ? 'Sim' : 'Não';
    }
    if (typeof value === 'object') {
        return JSON.stringify(value);
    }

    return values[field]?.[value] ?? String(value);
}

export function formatEvent(event) {
    return events[event] ?? event;
}

export function formatEntity(entity) {
    const name = entity?.split('\\').pop();

    return entities[name] ?? name;
}

export function renderValues(data) {
    const entries = Object.entries(data ?? {});

    if (!entries.length) {
        return $('<span>', { class: 'text-muted' }).text('—').prop('outerHTML');
    }

    const $list = $('<dl>', { class: 'mb-0 text-break' });
    entries.forEach(([field, value]) => {
        $list.append(
            $('<dt>', { class: 'fw-semibold' }).text(fields[field] ?? field.replaceAll('_', ' ')),
            $('<dd>', { class: 'mb-2' }).text(formatValue(value, field)),
        );
    });

    return $list.prop('outerHTML');
}

export function renderChanges(oldValues = {}, newValues = {}) {
    const changedFields = [...new Set([...Object.keys(oldValues ?? {}), ...Object.keys(newValues ?? {})])];
    const $body = $('<tbody>');

    changedFields.forEach((field) => {
        $body.append($('<tr>').append(
            $('<th>', { scope: 'row' }).text(fields[field] ?? field.replaceAll('_', ' ')),
            $('<td>').text(formatValue(oldValues?.[field], field)),
            $('<td>').text(formatValue(newValues?.[field], field)),
        ));
    });

    if (!changedFields.length) {
        $body.append($('<tr>').append(
            $('<td>', { colspan: 3, class: 'text-muted' }).text('Nenhum campo alterado.'),
        ));
    }

    return $('<div>', { class: 'table-responsive' }).append(
        $('<table>', { class: 'table table-sm table-bordered mb-0 text-break' }).append(
            $('<thead>').append($('<tr>').append(
                $('<th>', { scope: 'col' }).text('Campo'),
                $('<th>', { scope: 'col' }).text('Valor anterior'),
                $('<th>', { scope: 'col' }).text('Valor novo'),
            )),
            $body,
        ),
    );
}
