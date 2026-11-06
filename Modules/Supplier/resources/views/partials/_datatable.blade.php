@props([
    'id' => ''
])

<table
    id="{{ $id }}"
    class="table table-striped w-100"
    data-locale="{{ app()->getLocale() }}"
    data-source="{{ route('api.suppliers.index') }}"
    data-csrf-token="{{ csrf_token() }}"
>
    <thead>
        <tr>
            <th>Razão Social/Nome</th>
            <th>Nome Fantasia/Apelido</th>
            <th>CNPJ/CPF</th>
            <th>Ativo</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <!-- Linhas renderizadas pelo Blade -->
    </tbody>
</table>
