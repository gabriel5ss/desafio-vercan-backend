<?php

declare(strict_types=1);

namespace Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Valida os dados de cadastro e edição de usuários. */
final class UserRequest extends FormRequest
{
    /** Permite o acesso autenticado, seguindo o módulo Supplier. */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Define as regras, preservando a senha quando não informada na edição.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $user = $this->route('user');
        $user_id = $user?->getKey() ?? 'NULL';

        return [
            'name' => 'required|string|max:255',
            'email' => "required|email|max:255|unique:users,email,{$user_id},id",
            'password' => ($user === null ? 'required' : 'nullable').'|string|min:8|confirmed',
            'observation' => 'nullable|string|max:5000',
        ];
    }

    /**
     * Define os nomes dos campos exibidos nas mensagens de validação.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['name' => 'nome', 'email' => 'e-mail', 'password' => 'senha', 'observation' => 'observação'];
    }
}
