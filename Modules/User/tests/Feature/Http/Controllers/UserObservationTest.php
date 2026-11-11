<?php

declare(strict_types=1);

namespace Modules\User\Tests\Feature\Http\Controllers;

use Illuminate\Support\Facades\Context;
use Modules\User\Models\User;
use Modules\User\Models\UserObservation;
use OwenIt\Auditing\Models\Audit;
use RuntimeException;
use Tests\TestCase;

/** Verifica a observação relacionada e o agrupamento das alterações na auditoria. */
final class UserObservationTest extends TestCase
{
    private User $user;

    private User $target_user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->user = User::factory()->create();
        $this->target_user = User::factory()->create(['name' => 'Alfa', 'email' => 'alfa@teste.com']);
        config(['audit.console' => true]);
        Context::forget(['root_type', 'log_id']);
    }

    /**
     * Fornece as telas que exibem a observação relacionada.
     *
     * @return iterable<string, array{string}>
     */
    public static function pages(): iterable
    {
        yield 'edição' => ['user.edit'];
        yield 'consulta' => ['user.show'];
    }

    /** Confere o cadastro e o agrupamento do usuário com a observação. */
    public function test_store(): void
    {
        $data = ['name' => 'Beta', 'email' => 'beta@teste.com', 'password' => 'omega123', 'password_confirmation' => 'omega123', 'observation' => 'Observação Alfa'];

        $response = $this->actingAs($this->user)->post(route('user.store'), $data);

        $target_user = User::query()->where('email', 'beta@teste.com')->sole();
        $audits = Audit::query()->where('log_id', Context::get('log_id'))->get();
        $response->assertRedirectToRoute('user.index');
        $this->assertSame('Observação Alfa', $target_user->observation->content);
        $this->assertTrue($target_user->observation->user->is($target_user));
        $this->assertCount(2, $audits);
        $this->assertCount(1, $audits->pluck('log_id')->unique());
        $this->assertNotNull($audits->first()->log_id);
        $this->assertAuthenticatedAs($this->user);
    }

    /** Confere a alteração dos dois registros e os valores anterior e novo. */
    public function test_update(): void
    {
        $observation = UserObservation::factory()->for($this->target_user)->create(['content' => 'Alfa']);
        $data = ['name' => 'Beta', 'email' => 'alfa@teste.com', 'observation' => 'Omega'];

        $response = $this->actingAs($this->user)->put(route('user.update', $this->target_user), $data);

        $audits = Audit::query()->where('event', 'updated')->get();
        $audit = $audits->firstWhere('auditable_type', $observation->getMorphClass());
        $response->assertRedirectToRoute('user.index');
        $this->assertDatabaseHas('user_observations', ['id' => $observation->id, 'content' => 'Omega']);
        $this->assertCount(2, $audits);
        $this->assertCount(1, $audits->pluck('log_id')->unique());
        $this->assertSame($this->target_user->id, (int) $audit->root_id);
        $this->assertSame($this->target_user->getMorphClass(), $audit->root_type);
        $this->assertSame('Alfa', $audit->old_values['content']);
        $this->assertSame('Omega', $audit->new_values['content']);
        $this->assertAuthenticatedAs($this->user);
    }

    /** Confere a remoção da observação quando o campo é limpo. */
    public function test_remove(): void
    {
        $observation = UserObservation::factory()->for($this->target_user)->create();

        $response = $this->actingAs($this->user)->put(route('user.update', $this->target_user), ['name' => 'Alfa', 'email' => 'alfa@teste.com', 'observation' => '']);

        $response->assertRedirectToRoute('user.index');
        $this->assertModelMissing($observation);
        $this->assertDatabaseHas('audits', ['auditable_type' => $observation->getMorphClass(), 'auditable_id' => $observation->id, 'event' => 'deleted', 'root_id' => $this->target_user->id]);
        $this->assertAuthenticatedAs($this->user);
    }

    /** Confere que a omissão do campo preserva a observação existente. */
    public function test_update_without_observation(): void
    {
        $observation = UserObservation::factory()->for($this->target_user)->create(['content' => 'Omega']);

        $response = $this->actingAs($this->user)->put(route('user.update', $this->target_user), ['name' => 'Beta', 'email' => 'alfa@teste.com']);

        $response->assertRedirectToRoute('user.index');
        $this->assertDatabaseHas('user_observations', ['id' => $observation->id, 'content' => 'Omega']);
        $this->assertAuthenticatedAs($this->user);
    }

    /** Confere a exclusão auditada da observação junto com o usuário. */
    public function test_destroy(): void
    {
        $observation = UserObservation::factory()->for($this->target_user)->create();

        $response = $this->actingAs($this->user)->delete(route('user.destroy', $this->target_user));

        $audits = Audit::query()->where('event', 'deleted')->get();
        $response->assertRedirectToRoute('user.index');
        $this->assertModelMissing($observation);
        $this->assertModelMissing($this->target_user);
        $this->assertCount(2, $audits);
        $this->assertCount(1, $audits->pluck('log_id')->unique());
        $this->assertAuthenticatedAs($this->user);
    }

    /** Confere o rollback do usuário quando a gravação da observação falha. */
    public function test_update_failed(): void
    {
        UserObservation::saving(static function (): void {
            throw new RuntimeException('Falha ao salvar observação.');
        });

        $response = $this->actingAs($this->user)->put(route('user.update', $this->target_user), [
            'name' => 'Beta', 'email' => 'alfa@teste.com', 'observation' => 'Omega',
        ]);

        $response->assertServerError();
        $this->assertDatabaseHas('users', ['id' => $this->target_user->id, 'name' => 'Alfa']);
        $this->assertDatabaseMissing('user_observations', ['user_id' => $this->target_user->id]);
        $this->assertDatabaseMissing('audits', ['event' => 'updated', 'auditable_id' => $this->target_user->id]);
        $this->assertAuthenticatedAs($this->user);
    }

    /** Confere o rollback da observação quando a exclusão do usuário falha. */
    public function test_destroy_failed(): void
    {
        $observation = UserObservation::factory()->for($this->target_user)->create(['content' => 'Omega']);
        User::deleting(static function (): void {
            throw new RuntimeException('Falha ao excluir usuário.');
        });

        $response = $this->actingAs($this->user)->delete(route('user.destroy', $this->target_user));

        $response->assertServerError();
        $this->assertModelExists($this->target_user);
        $this->assertModelExists($observation);
        $this->assertDatabaseMissing('audits', ['event' => 'deleted']);
        $this->assertAuthenticatedAs($this->user);
    }
}
