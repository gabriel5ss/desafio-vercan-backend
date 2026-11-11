<table
    id="{{ $id }}"
    class="table table-striped w-100"
    data-locale="{{ app()->getLocale() }}"
    data-source="{{ route('api.audits.index') }}"
>
    <thead>
        <tr>
            <th>Data</th>
            <th>Usuário</th>
            <th>Evento</th>
            <th>Entidade</th>
            <th>ID do registro</th>
            <th>IP</th>
            <th>Valores anteriores</th>
            <th>Valores novos</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>
