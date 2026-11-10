<?php

declare(strict_types=1);

namespace Modules\Supplier\Models;

use App\Models\City;
use App\Models\State;
use Coderflex\LaravelPresenter\Concerns\CanPresent;
use Coderflex\LaravelPresenter\Concerns\UsesPresenters;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Supplier\Database\Factories\SupplierFactory;
use Modules\Supplier\Presenters\SupplierPresenter;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @property-read string|null $name
 * @property-read string $trade_name
 * @property-read string|null $document
 * @property-read Company|Individual|null $supplierable
 */
#[UseFactory(SupplierFactory::class)]
final class Supplier extends Model implements Auditable, CanPresent
{
    use AuditableTrait;

    /** @use HasFactory<SupplierFactory> */
    use HasFactory;

    use UsesPresenters;

    protected $presenters = [
        'default' => SupplierPresenter::class,
    ];

    protected $fillable = [
        'state_id', 'city_id', 'active', 'postal_code', 'street', 'number', 'complement',
        'district', 'reference', 'state', 'city', 'condominium', 'condominium_address', 'condominium_number', 'observation',
    ];

    /** @return array{states: Collection<int, State>, cities: Collection<int, City>} */
    public static function formOptions(): array
    {
        $states = State::orderBy('name')->get(['id', 'code', 'name']);
        $cities = City::orderBy('name')->get(['id', 'state_id', 'name']);

        return [
            'states' => $states,
            'cities' => $cities,
        ];
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

    protected function casts(): array
    {
        return ['active' => 'boolean', 'condominium' => 'boolean'];
    }

    /** @return Attribute<string|null, never> */

    // usar isso
    // getFormattedNameAttribute

    // não usar isso
    // name
    protected function name(): Attribute
    {
        // não usar isso
        return Attribute::make(
            get: fn (): ?string => $this->supplierable instanceof Individual
                ? $this->supplierable->name
                : $this->supplierable?->corporate_name,
        );

        // usar isso
        // $name = $this->supplierable instanceof Individual
        //     ? $this->supplierable->name
        //     : $this->supplierable?->corporate_name;

        // return $name;
    }

    /** @return Attribute<string, never> */
    protected function tradeName(): Attribute
    {
        return Attribute::make(
            get: fn (): string => $this->supplierable instanceof Individual
                ? ($this->supplierable->nickname ?? '')
                : ($this->supplierable->trade_name ?? ''),
        );
    }

    /** @return Attribute<string|null, never> */
    protected function document(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => $this->supplierable instanceof Individual
                ? $this->supplierable->cpf
                : $this->supplierable?->cnpj,
        );
    }

    /** @param Builder<Supplier> $query */
    #[Scope]
    protected function applySorting(Builder $query, ?string $sortBy, string $direction): void
    {
        if ($sortBy === null) {
            $query->latest();

            return;
        }

        if ($sortBy === 'active') {
            $query->orderBy('suppliers.active', $direction);
        } else {
            $fields = [
                'name' => ['corporate_name', 'name'],
                'trade_name' => ['trade_name', 'nickname'],
                'document' => ['cnpj', 'cpf'],
            ];
            [$companyField, $individualField] = $fields[$sortBy] ?? $fields['name'];

            $query->orderByRaw(
                "CASE WHEN suppliers.supplierable_type = ? THEN (SELECT companies.{$companyField} FROM companies WHERE companies.id = suppliers.supplierable_id) ELSE (SELECT individuals.{$individualField} FROM individuals WHERE individuals.id = suppliers.supplierable_id) END {$direction}",
                ['company'],
            );
        }

        $query->orderBy('suppliers.id');
    }

    /** @param Builder<Supplier> $query */
    #[Scope]
    protected function searchTerm(Builder $query, ?string $search): void
    {
        $search = mb_trim($search ?? '');

        if ($search === '') {
            return;
        }

        $query->where(function (Builder $query) use ($search): void {
            $query->whereHasMorph(
                'supplierable',
                [Individual::class],
                function (Builder $personQuery) use ($search): void {
                    $personQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('cpf', 'like', "%{$search}%")
                        ->orWhere('nickname', 'like', "%{$search}%");
                },
            )->orWhereHasMorph(
                'supplierable',
                [Company::class],
                function (Builder $personQuery) use ($search): void {
                    $personQuery->where('corporate_name', 'like', "%{$search}%")
                        ->orWhere('trade_name', 'like', "%{$search}%")
                        ->orWhere('cnpj', 'like', "%{$search}%");
                },
            );
        });
    }
}
