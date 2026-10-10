<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contact extends Model
{
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
