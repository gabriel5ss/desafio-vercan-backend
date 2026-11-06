<?php

namespace Modules\Supplier\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class Contact extends Model implements Auditable
{
    use AuditableTrait;

    protected $fillable = ['is_primary', 'name', 'company', 'position'];

    protected function casts(): array
    {
        return ['is_primary' => 'boolean'];
    }

    /** @return HasMany<ContactMethod, $this> */
    public function methods(): HasMany
    {
        return $this->hasMany(ContactMethod::class);
    }
}
