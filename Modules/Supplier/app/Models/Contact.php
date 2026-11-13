<?php

declare(strict_types=1);

namespace Modules\Supplier\Models;

use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Supplier\Database\Factories\ContactFactory;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

#[UseFactory(ContactFactory::class)]
class Contact extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<ContactFactory> */
    use HasFactory;

    protected $fillable = ['is_primary', 'name', 'company', 'position'];

    /** @return HasMany<ContactMethod, $this> */
    public function methods(): HasMany
    {
        return $this->hasMany(ContactMethod::class);
    }

    protected function casts(): array
    {
        return ['is_primary' => 'boolean'];
    }
}
