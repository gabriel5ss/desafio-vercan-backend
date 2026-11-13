<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\StateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class State extends Model
{
    /** @use HasFactory<StateFactory> */
    use HasFactory;

    protected $fillable = ['ibge_code', 'code', 'name'];

    /** @return HasMany<City, $this> */
    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }
}
