<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property-read Company|Individual|null $supplierable
 */
class Supplier extends Model
{
    protected $table = 'suppliers';

    protected $fillable = [
        'state_id', 'city_id', 'active', 'postal_code', 'street', 'number', 'complement',
        'district', 'reference', 'state', 'city', 'condominium', 'condominium_address', 'condominium_number', 'observation',
    ];

    protected function casts(): array
    {
        return ['active' => 'boolean', 'condominium' => 'boolean'];
    }

    /** @return MorphTo<Model, $this> */
    public function supplierable(): MorphTo
    {
        return $this->morphTo('supplierable', 'supplierable_type', 'supplierable_id');
    }

    /** @return BelongsTo<State, $this> */
    public function stateInfo(): BelongsTo
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    /** @return BelongsTo<City, $this> */
    public function cityInfo(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    /** @return HasMany<Contact, $this> */
    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }
}
