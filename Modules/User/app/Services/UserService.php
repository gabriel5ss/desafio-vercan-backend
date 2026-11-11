<?php

declare(strict_types=1);

namespace Modules\User\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Modules\User\Models\User;
use Throwable;

/** Salva o usuário e sua observação como uma única operação auditável. */
final class UserService
{
    /**
     * Persiste os dados do usuário e a observação opcional em transação.
     *
     * @param array<string, mixed> $data Dados validados do formulário.
     * @param User $user Usuário novo ou existente.
     *
     * @return User Usuário salvo.
     */
    public function save(array $data, User $user): User
    {
        DB::beginTransaction();

        try {
            $attributes = Arr::only($data, ['name', 'email']);

            if (! empty($data['password'])) {
                $attributes['password'] = $data['password'];
            }

            $user->fill($attributes)->save();
            $this->saveObservation($data, $user);

            DB::commit();

            return $user;
        } catch (Throwable $exception) {
            DB::rollBack();

            throw $exception;
        }
    }

    /**
     * Exclui os registros individualmente para auditar também a observação.
     *
     * @param User $user Usuário removido.
     */
    public function delete(User $user): void
    {
        DB::beginTransaction();

        try {
            $user->observation?->delete();
            $user->delete();

            DB::commit();
        } catch (Throwable $exception) {
            DB::rollBack();

            throw $exception;
        }
    }

    /**
     * Mantém uma observação por usuário; campo ausente preserva o registro e vazio o remove.
     *
     * @param array<string, mixed> $data Dados validados do formulário.
     * @param User $user Usuário proprietário da observação.
     */
    private function saveObservation(array $data, User $user): void
    {
        if (! array_key_exists('observation', $data)) {
            return;
        }

        if (blank($data['observation'])) {
            $user->observation?->delete();

            return;
        }

        $user->observation()->updateOrCreate([], ['content' => $data['observation']]);
    }
}
