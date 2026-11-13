<?php

declare(strict_types=1);

namespace Modules\User\Tests\Feature\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use Modules\User\Models\User;
use Tests\TestCase;

/** Testa as telas e operações do CRUD de usuários. */
class UserControllerTest extends TestCase
{
    private User $user;

    private User $target_user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->user = User::factory()->create();
        $this->target_user = User::factory()->create(['name' => 'Alfa', 'email' => 'alfa@teste.com']);
    }

    /**
     * Fornece os principais erros do cadastro.
     *
     * @return iterable<string, array{array<string, string>, list<string>}>
     */
    public static function invalid_data(): iterable
    {
        yield 'obrigatórios' => [[], ['name', 'email', 'password']];
        yield 'e-mail duplicado' => [['name' => 'Beta', 'email' => 'alfa@teste.com', 'password' => 'omega123', 'password_confirmation' => 'omega123'], ['email']];
        yield 'senha curta' => [['name' => 'Beta', 'email' => 'beta@teste.com', 'password' => 'omega', 'password_confirmation' => 'omega'], ['password']];
        yield 'confirmação diferente' => [['name' => 'Beta', 'email' => 'beta@teste.com', 'password' => 'omega123', 'password_confirmation' => 'beta1234'], ['password']];
    }

    /** Confere os dados e as ações da listagem. */
    public function test_index(): void
    {
        $response = $this->actingAs($this->user)->get(route('user.index'));

        $response->assertSuccessful();
        $response->assertSee('Alfa');
        $response->assertSee('alfa@teste.com');
        $response->assertSee(route('user.create'));
        $response->assertSee(route('user.destroy', $this->target_user));
        $response->assertSee('mdi mdi-eye-outline');
        $response->assertSee('mdi mdi-pencil-outline');
        $response->assertSee('mdi mdi-delete-outline');
        $this->assertAuthenticatedAs($this->user);
    }

    /** Confere o formulário de cadastro. */
    public function test_create(): void
    {
        $response = $this->actingAs($this->user)->get(route('user.create'));

        $response->assertSuccessful();
        $response->assertSee('Novo usuário');
        $response->assertSee('password_confirmation');
        $this->assertAuthenticatedAs($this->user);
    }

    /** Confere o formulário de edição. */
    public function test_edit(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('user.edit', $this->target_user));

        $response->assertSuccessful();
        $response->assertSee('Alfa');
        $response->assertSee('Deixe em branco para manter a senha atual.');
        $response->assertDontSee($this->target_user->password);
        $this->assertAuthenticatedAs($this->user);
    }

    /** Confere a consulta sem exposição de senha. */
    public function test_show(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('user.show', $this->target_user));

        $response->assertSuccessful();
        $response->assertSee('Alfa');
        $response->assertSee('disabled');
        $response->assertSee('Histórico de alterações');
        $response->assertSeeInOrder(['Histórico de alterações', 'Voltar']);
        $response->assertDontSee('>Salvar</button>', false);
        $response->assertSee('data-audit-history-table', false);
        $response->assertSee(route('api.audits.history', [
            'root_type' => $this->target_user->getMorphClass(),
            'root_id' => $this->target_user->id,
        ]));
        $response->assertDontSee('name="password"', false);
        $response->assertDontSee($this->target_user->password);
        $this->assertAuthenticatedAs($this->user);
    }

    /** Confere o cadastro com senha criptografada. */
    public function test_store(): void
    {
        $data = ['name' => 'Beta', 'email' => 'beta@teste.com', 'password' => 'omega123', 'password_confirmation' => 'omega123'];

        $response = $this->actingAs($this->user)->post(route('user.store'), $data);

        $response->assertRedirectToRoute('user.index');
        $response->assertSessionHas('status', 'Usuário cadastrado com sucesso.');
        $this->assertDatabaseHas('users', ['name' => 'Beta', 'email' => 'beta@teste.com']);
        $this->assertTrue(Hash::check('omega123', User::query()->where('email', 'beta@teste.com')->sole()->password));
        $this->assertAuthenticatedAs($this->user);
    }

    /** Confere a edição sem trocar senha ou e-mail. */
    public function test_update(): void
    {
        $password = $this->target_user->password;
        $data = [
            'name' => 'Beta', 'email' => 'alfa@teste.com',
            'password' => '', 'password_confirmation' => '',
        ];

        $response = $this->actingAs($this->user)
            ->put(route('user.update', $this->target_user), $data);

        $response->assertRedirectToRoute('user.index');
        $response->assertSessionHas('status', 'Usuário atualizado com sucesso.');
        $this->assertDatabaseHas('users', ['id' => $this->target_user->id, 'name' => 'Beta', 'email' => 'alfa@teste.com', 'password' => $password]);
        $this->assertAuthenticatedAs($this->user);
    }

    /** Confere a troca de senha na edição. */
    public function test_update_password(): void
    {
        $data = [
            'name' => 'Alfa', 'email' => 'beta@teste.com',
            'password' => 'omega456', 'password_confirmation' => 'omega456',
        ];

        $response = $this->actingAs($this->user)
            ->put(route('user.update', $this->target_user), $data);

        $response->assertRedirectToRoute('user.index');
        $this->assertTrue(Hash::check('omega456', $this->target_user->fresh()->password));
        $this->assertDatabaseHas('users', ['id' => $this->target_user->id, 'email' => 'beta@teste.com']);
        $this->assertAuthenticatedAs($this->user);
    }

    /** Confere a remoção de outra conta. */
    public function test_destroy(): void
    {
        $response = $this->actingAs($this->user)->delete(route('user.destroy', $this->target_user));

        $response->assertRedirectToRoute('user.index');
        $response->assertSessionHas('status', 'Usuário removido com sucesso.');
        $this->assertModelMissing($this->target_user);
        $this->assertAuthenticatedAs($this->user);
    }
}
