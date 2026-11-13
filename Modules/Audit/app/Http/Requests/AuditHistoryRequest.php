<?php

declare(strict_types=1);

namespace Modules\Audit\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Valida a paginação do histórico por operações completas. */
class AuditHistoryRequest extends FormRequest
{
    /** Mantém o acesso autenticado definido nas rotas dos módulos consumidores. */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Limita a página e mantém a busca desativada, como no DataTables da tela.
     *
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'draw' => 'required|integer|min:0',
            'start' => 'required|integer|min:0',
            'length' => 'required|integer|min:1|max:100',
            'search' => 'sometimes|array',
            'search.value' => 'sometimes|nullable|string|max:0',
            'columns' => 'sometimes|array|list|size:3',
            'columns.*.data' => 'required|in:created_at,user_name,audit',
            'columns.*.name' => 'sometimes|nullable|in:created_at,user_name,audit',
            'columns.*.search.value' => 'sometimes|nullable|string|max:0',
        ];
    }
}
