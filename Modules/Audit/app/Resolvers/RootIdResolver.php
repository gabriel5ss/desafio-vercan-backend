<?php

declare(strict_types=1);

namespace Modules\Audit\Resolvers;

use Illuminate\Support\Facades\Context;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Contracts\Resolver;

/** Resolve o identificador da entidade raiz como um valor individual. */
final class RootIdResolver implements Resolver
{
    /**
     * Retorna o identificador da entidade raiz para a coluna da auditoria.
     *
     * @param Auditable $auditable Entidade auditada.
     *
     * @return int|string|null Valor da coluna.
     */
    public static function resolve(Auditable $auditable): int|string|null
    {
        $root = Context::get('root_type');

        return $root?->getKey();
    }
}
