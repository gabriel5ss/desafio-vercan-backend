<?php

declare(strict_types=1);

namespace Modules\User\Tests\Unit\Http\Requests;

use Illuminate\Routing\Route;
use Modules\User\Http\Requests\UserRequest;
use Modules\User\Models\User;
use PHPUnit\Framework\TestCase;

/** Testa as regras de cadastro e edição de usuários. */
final class UserRequestTest extends TestCase
{
    private UserRequest $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->request = new UserRequest();
    }

    /** Confere as regras obrigatórias do cadastro. */
    public function test_rules(): void
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,NULL,id',
            'password' => 'required|string|min:8|confirmed',
            'observation' => 'nullable|string|max:5000',
        ];

        $this->assertSame($rules, $this->request->rules());
    }

    /** Confere a senha opcional e a exceção do e-mail na edição. */
    public function test_update_rules(): void
    {
        $user = new User();
        $user->id = 1;
        $route = new Route('PUT', 'users/{user}', []);
        $route->bind($this->request);
        $route->setParameter('user', $user);
        $this->request->setRouteResolver(fn (): Route => $route);
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,1,id',
            'password' => 'nullable|string|min:8|confirmed',
            'observation' => 'nullable|string|max:5000',
        ];

        $this->assertSame($rules, $this->request->rules());
    }
}
