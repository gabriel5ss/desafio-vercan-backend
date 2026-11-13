<?php

declare(strict_types=1);

namespace Modules\Audit\Tests\Unit\Http\Requests;

use Modules\Audit\Http\Requests\AuditHistoryRequest;
use PHPUnit\Framework\TestCase;

/** Confere os parâmetros aceitos pela tabela de operações de auditoria. */
class AuditHistoryRequestTest extends TestCase
{
    private AuditHistoryRequest $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->request = new AuditHistoryRequest();
    }

    /** Confere a paginação limitada e a busca desativada da tabela. */
    public function test_rules(): void
    {
        $rules = [
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

        $this->assertSame($rules, $this->request->rules());
    }
}
