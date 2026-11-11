<?php

declare(strict_types=1);

namespace Modules\User\Tests\Feature\Models;

use Database\Factories\UserFactory;
use Modules\Supplier\Models\Supplier;
use Modules\User\Models\User;
use OwenIt\Auditing\Models\Audit;
use Tests\TestCase;

/** Verifica a factory e os vínculos persistidos antes da mudança de namespace. */
final class UserTest extends TestCase
{
    /** Confere a criação direta pela factory existente. */
    public function test_factory(): void
    {
        $user = UserFactory::new()->create(['name' => 'Alfa']);

        $this->assertInstanceOf(User::class, $user);
        $this->assertModelExists($user);
    }

    /** Confere a resolução do autor de uma auditoria antiga. */
    public function test_audit_user(): void
    {
        $user = User::factory()->create();
        $supplier = Supplier::factory()->create();
        $audit = Audit::query()->create([
            'auditable_type' => $supplier->getMorphClass(), 'auditable_id' => $supplier->id,
            'user_type' => 'Modules\\User\\Models\\User', 'user_id' => $user->id,
            'event' => 'updated', 'old_values' => [], 'new_values' => ['name' => 'Alfa'],
        ]);

        $this->assertTrue($audit->fresh()->user->is($user));
    }
}
