<?php

declare(strict_types=1);

namespace Modules\User\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\User\Database\Factories\UserObservationFactory;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/** Armazena a observação opcional do usuário em um registro auditável próprio. */
#[Fillable(['content'])]
#[UseFactory(UserObservationFactory::class)]
class UserObservation extends Model implements Auditable
{
    /** @use HasFactory<UserObservationFactory> */
    use AuditableTrait, HasFactory;

    /**
     * Identifica o usuário ao qual a observação pertence.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
