<?php

declare(strict_types=1);

namespace Modules\Audit\Resolvers;

use Illuminate\Support\Facades\Context;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Contracts\Resolver;

/** Resolve o tipo da entidade raiz como um valor individual. */
final class RootTypeResolver implements Resolver
{
    /**
     * Retorna o tipo da entidade raiz para a coluna da auditoria.
     *
     * @param  Auditable  $auditable  Entidade auditada.
     * @return ?string Valor da coluna.
     */
    public static function resolve(Auditable $auditable): ?string
    {
        $root = Context::get('root_type');

        return $root?->getMorphClass();
    }
}
