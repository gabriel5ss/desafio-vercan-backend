<?php

declare(strict_types=1);

namespace Modules\Audit\Tests\Feature\Http\Controllers;

use Modules\Supplier\Models\Supplier;
use Modules\User\Models\User;
use Modules\User\Models\UserObservation;
use OwenIt\Auditing\Models\Audit;
use Tests\TestCase;

/** Verifica a preservação das rotas e da consulta de auditorias. */
class AuditControllerTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->user = User::factory()->create(['name' => 'Alfa']);
    }

    /** Confere a tela existente e o endereço usado pela tabela. */
    public function test_index(): void
    {
        $response = $this->actingAs($this->user)->get(route('audits.index'));

        $response->assertSuccessful();
        $response->assertSee('Logs de auditoria');
        $response->assertSee(route('api.audits.index'));
        $this->assertAuthenticatedAs($this->user);
    }

    /** Confere a listagem JSON e a resolução do autor. */
    public function test_index_api(): void
    {
        $supplier = Supplier::factory()->create();
        Audit::query()->create([
            'auditable_type' => $supplier->getMorphClass(), 'auditable_id' => $supplier->id,
            'user_type' => $this->user->getMorphClass(), 'user_id' => $this->user->id,
            'event' => 'updated', 'old_values' => [], 'new_values' => ['name' => 'Beta'],
        ]);
        $query = ['draw' => 1, 'start' => 0, 'length' => 10, 'columns' => [
            ['data' => 'created_at'], ['data' => 'user_name'], ['data' => 'event'],
            ['data' => 'auditable_type'], ['data' => 'auditable_id'], ['data' => 'ip_address'],
            ['data' => 'old_values', 'name' => '', 'orderable' => 'false', 'searchable' => 'false'],
            ['data' => 'new_values', 'name' => 'new_values', 'orderable' => 'false', 'searchable' => 'false'],
        ]];

        $response = $this->actingAs($this->user)->getJson(route('api.audits.index', $query));

        $response->assertSuccessful();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.user_name', 'Alfa');
        $response->assertJsonPath('data.0.new_values.name', 'Beta');
        $this->assertAuthenticatedAs($this->user);
    }

    /** Confere o histórico de usuário com uma observação relacionada pela API compartilhada. */
    public function test_history(): void
    {
        $observation = UserObservation::factory()->for($this->user)->create(['content' => 'Omega']);
        Audit::query()->create([
            'root_type' => $this->user->getMorphClass(), 'root_id' => $this->user->id,
            'log_id' => '11111111-1111-4111-8111-111111111111',
            'auditable_type' => $observation->getMorphClass(), 'auditable_id' => $observation->id,
            'user_type' => $this->user->getMorphClass(), 'user_id' => $this->user->id,
            'event' => 'created', 'old_values' => [], 'new_values' => ['content' => 'Omega'],
        ]);
        $query = ['root_type' => $this->user->getMorphClass(), 'root_id' => $this->user->id, 'draw' => 1, 'start' => 0, 'length' => 20];

        $response = $this->actingAs($this->user)->getJson(route('api.audits.history', $query));

        $response->assertSuccessful();
        $response->assertJsonPath('recordsTotal', 1);
        $response->assertJsonPath('data.0.user_name', 'Alfa');
        $response->assertJsonPath('data.0.audit.0.changes.type', 'UserObservation');
        $response->assertJsonPath('data.0.audit.0.changes.new_values.content', 'Omega');
        $this->assertAuthenticatedAs($this->user);
    }
}
