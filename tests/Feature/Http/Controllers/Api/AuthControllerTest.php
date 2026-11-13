<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use Modules\User\Models\User;
use Tests\TestCase;

/** Verifica a autenticação com a model localizada no módulo User. */
class AuthControllerTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['email' => 'alfa@teste.com', 'password' => 'omega123']);
    }

    /** Confere que o provider autentica a model do módulo. */
    public function test_login(): void
    {
        $response = $this->post(route('access_login'), ['email' => 'alfa@teste.com', 'password' => 'omega123']);

        $response->assertRedirectToRoute('dashboard');
        $this->assertAuthenticatedAs($this->user);
    }

    /** Confere que uma senha incorreta mantém o visitante desautenticado. */
    public function test_login_failed(): void
    {
        $response = $this->postJson(route('access_login'), ['email' => 'alfa@teste.com', 'password' => 'beta1234']);

        $response->assertUnprocessable();
        $response->assertInvalid(['email']);
        $this->assertGuest();
    }
}
