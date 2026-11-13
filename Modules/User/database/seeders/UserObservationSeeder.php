<?php

declare(strict_types=1);

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\User\Models\User;

/** Adiciona uma observação de demonstração ao primeiro usuário existente. */
class UserObservationSeeder extends Seeder
{
    /** Cria o exemplo somente quando houver um usuário e nenhuma observação relacionada. */
    public function run(): void
    {
        $user = User::query()->first();

        if ($user === null) {
            return;
        }

        $user->observation()->firstOrCreate([], ['content' => 'Observação de demonstração para testar a auditoria.']);
    }
}
