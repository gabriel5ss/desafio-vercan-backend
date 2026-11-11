<x-form.card title="Histórico de alterações" :id="'audit-history-'.md5($root_type.':'.$root_id)" :collapsed="false">
    <div data-audit-history>
        <p class="text-muted">Cada grupo reúne as alterações realizadas na mesma operação.</p>
        <div class="table-responsive">
            <table
                class="table table-striped table-bordered w-100"
                data-audit-history-table
                data-source="{{ route('api.audits.history', ['root_type' => $root_type, 'root_id' => $root_id]) }}"
            >
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Usuário</th>
                        <th>Alterações</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
        <p data-audit-history-error class="text-danger d-none" role="alert">
            Não foi possível carregar o histórico. Tente novamente.
        </p>
    </div>
</x-form.card>

@pushOnce('scripts', 'audit-assets')
    @vite('Modules/Audit/resources/assets/js/app.js')
@endPushOnce
