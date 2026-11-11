<?php

declare(strict_types=1);

namespace Modules\Audit\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use OwenIt\Auditing\Models\Audit;
use Yajra\DataTables\Facades\DataTables;

/** Consulta o histórico paginado de qualquer model usada como raiz da auditoria. */
final class AuditHistoryService
{
    /**
     * Pagina operações completas e inclui os detalhes dos grupos da página atual.
     *
     * @param string $root_type Tipo da raiz gravado nos logs.
     * @param int $root_id Identificador da raiz gravado nos logs.
     *
     * @return JsonResponse Resposta no protocolo do DataTables.
     */
    public function table(string $root_type, int $root_id): JsonResponse
    {
        $history = Audit::query()
            ->where('root_type', $root_type)
            ->where('root_id', $root_id)
            ->whereNotNull('log_id');

        // Pagina operações completas, sem dividir suas alterações entre páginas.
        $table = DataTables::eloquent((clone $history)
            ->select('log_id')
            ->selectRaw('MAX(created_at) as created_at')
            ->selectRaw('MAX(id) as last_audit_id')
            ->groupBy('log_id'))
            ->filter(static function (): void {
            })
            ->order(static function (Builder $query): void {
                $query->orderByDesc('created_at')->orderByDesc('last_audit_id');
            });

        $groups = $table->prepareQuery()->results();

        // Carrega em lote somente as alterações dos grupos da página atual.
        $audits = (clone $history)->with('user')
            ->whereIn('log_id', $groups->pluck('log_id'))
            ->orderBy('id')
            ->get()
            ->groupBy('log_id');

        return $table
            ->addColumn('group_id', fn (Audit $group): string => $group->getAttribute('log_id'))
            ->editColumn('created_at', fn (Audit $group): ?string => $group->created_at?->format('d/m/Y H:i:s'))
            ->addColumn('user_name', fn (Audit $group): ?string => $audits->get($group->getAttribute('log_id'))?->first()?->user?->getAttribute('name'))
            ->addColumn('audit', fn (Audit $group): array => $audits->get($group->getAttribute('log_id'), collect())
                ->map(fn (Audit $audit): array => $this->formatAudit($audit))->all())
            ->only(['group_id', 'created_at', 'user_name', 'audit'])
            ->escapeColumns([])
            ->toJson();
    }

    /**
     * Formata uma alteração para o histórico.
     *
     * @return array{event: string, auditable_type: string, changes: array{type: string, id: int|string, old_values: array<string, mixed>, new_values: array<string, mixed>}}
     */
    private function formatAudit(Audit $audit): array
    {
        return [
            'event' => $audit->event,
            'auditable_type' => $audit->auditable_type,
            'changes' => [
                'type' => class_basename($audit->auditable_type),
                'id' => $audit->auditable_id,
                'old_values' => $audit->old_values,
                'new_values' => $audit->new_values,
            ],
        ];
    }
}
