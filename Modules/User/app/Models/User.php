<?php

declare(strict_types=1);

namespace Modules\User\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
#[UseFactory(UserFactory::class)]
class User extends Authenticatable implements Auditable
{
    /** @use HasFactory<UserFactory> */
    use AuditableTrait, HasApiTokens, HasFactory, Notifiable;

    /**
     * Retorna a observação opcional armazenada em tabela relacionada.
     *
     * @return HasOne<UserObservation, $this>
     */
    public function observation(): HasOne
    {
        return $this->hasOne(UserObservation::class);
    }

    /**
     * Retorna o nome completo do usuario.
     *
     * @return string
     */
    public function fullName(): string
    {
        if ($this->last_name !== null) {
            return $this->name.' '.$this->last_name;
        }

        return $this->name.' - ';
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
