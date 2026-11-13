<?php

declare(strict_types=1);

namespace Modules\Audit\Resolvers;

use Illuminate\Support\Facades\Context;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Contracts\Resolver;

/** Resolve o identificador da operação como um valor individual. */
class LogIdResolver implements Resolver
{
    /**
     * Retorna o identificador da operação para a coluna da auditoria.
     *
     * @param Auditable $auditable Entidade auditada.
     *
     * @return ?string Valor da coluna.
     */
    public static function resolve(?Auditable $auditable = null): ?string
    {
        return Context::get('log_id');
    }
}
