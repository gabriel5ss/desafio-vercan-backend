<?php

declare(strict_types=1);

namespace Modules\Audit\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\JsonResponse;
use Modules\Audit\Http\Requests\AuditIndexRequest;
use Modules\Audit\Services\AuditHistoryService;
use Modules\User\Models\User;
use OwenIt\Auditing\Models\Audit;
use Yajra\DataTables\Facades\DataTables;

class AuditController extends Controller
{
    public function index(): View
    {
        return view('audit::index');
    }

    /**
     * Consulta o histórico pelos identificadores da raiz informados na URL.
     *
     * @param string $root_type Tipo gravado em root_type, sem resolver ou instanciar classes.
     * @param int $root_id Identificador gravado em root_id.
     * @param AuditHistoryService $service Consulta paginada compartilhada.
     *
     * @return JsonResponse Operações completas no protocolo do DataTables.
     */
    public function history(string $root_type, int $root_id, AuditHistoryService $service): JsonResponse
    {
        return $service->table($root_type, $root_id);
    }

    public function indexApi(AuditIndexRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $search = mb_trim((string) ($filters['search']['value'] ?? ''));
        $order = $filters['order'][0] ?? null;
        $columns = [
            'audits.created_at',
            'users.name',
            'audits.event',
            'audits.auditable_type',
            'audits.auditable_id',
            'audits.ip_address',
            'audits.log_id',
            'audits.old_values',
            'audits.new_values',
        ];

        $query = Audit::query()
            ->leftJoin('users', function (JoinClause $join): void {
                $join->on('users.id', '=', 'audits.user_id')
                    ->where('audits.user_type', (new User())->getMorphClass());
            })
            ->select([
                'audits.id',
                'audits.created_at',
                'users.name as user_name',
                'audits.event',
                'audits.auditable_type',
                'audits.auditable_id',
                'audits.ip_address',
                'audits.log_id',
                'audits.old_values',
                'audits.new_values',
            ]);

        return DataTables::eloquent($query)
            ->filter(function (Builder $query) use ($search, $columns): void {
                if ($search === '') {
                    return;
                }

                $query->where(function (Builder $query) use ($search, $columns): void {
                    foreach ($columns as $column) {
                        $query->orWhere($column, 'like', '%'.$search.'%');
                    }
                });
            })
            ->order(function (Builder $query) use ($order, $columns): void {
                $query->orderBy($columns[$order['column'] ?? 0], $order['dir'] ?? 'desc')
                    ->orderByDesc('audits.id');
            })
            ->editColumn('created_at', fn (Audit $audit): ?string => $audit->created_at?->format('d/m/Y H:i:s'))
            ->editColumn('auditable_type', fn (Audit $audit): string => class_basename($audit->auditable_type))
            ->only(['created_at', 'user_name', 'event', 'auditable_type', 'auditable_id', 'ip_address', 'old_values', 'new_values'])
            ->escapeColumns([])
            ->toJson();
    }
}
