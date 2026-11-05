<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property-read string|null $name
 * @property-read string $trade_name
 * @property-read string|null $document
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

    /** @return Attribute<string|null, never> */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => $this->supplierable instanceof Individual
                ? $this->supplierable->name
                : $this->supplierable?->corporate_name,
        );
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
        $search = trim($search ?? '');

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

    /**
     * Prepara os dados do fornecedor para preencher o formulário.
     *
     * @return array<string, mixed>
     */
    public static function formData(?Supplier $supplier = null): array
    {
        $supplier?->loadMissing(['supplierable', 'contacts.methods']);

        $person = $supplier?->supplierable;
        $primaryContact = $supplier?->contacts
            ->firstWhere('is_primary', true);

        $contactValues = [];
        $contactTemplates = [];
        $nextContactIndexes = [];
        $emptyRow = [['value' => '', 'label' => '']];

        foreach (['phones' => 'phone', 'emails' => 'email'] as $field => $kind) {
            $savedValues = $primaryContact?->methods
                ->where('kind', $kind)
                ->map(fn (ContactMethod $method): array => [
                    'value' => $method->value,
                    'label' => $method->label,
                ])
                ->values()
                ->all() ?? [];

            $defaultValues = $savedValues === [] ? $emptyRow : $savedValues;

            $rows = old("primary_contact.{$field}", $defaultValues);
            $rows = $rows === [] ? $emptyRow : $rows;

            foreach ($rows as $index => $row) {
                $rows[$index]['value_field'] = "primary_contact.{$field}.{$index}.value";
                $rows[$index]['label_field'] = "primary_contact.{$field}.{$index}.label";
            }

            $contactValues[$field] = $rows;
            $contactTemplates[$field] = [
                'value_field' => "primary_contact.{$field}.__INDEX__.value",
                'label_field' => "primary_contact.{$field}.__INDEX__.label",
            ];
            $nextContactIndexes[$field] = max(array_keys($rows)) + 1;
        }

        $savedAdditionalContacts = $supplier !== null
            ? $supplier->contacts->where('is_primary', false)->values()->map(function (Contact $contact): array {
                return [
                    ...$contact->only(['name', 'company', 'position']),
                    'phones' => $contact->methods->where('kind', 'phone')->values()->map->only(['value', 'label'])->all(),
                    'emails' => $contact->methods->where('kind', 'email')->values()->map->only(['value', 'label'])->all(),
                ];
            })->all()
            : [];
        $additionalContacts = session()->hasOldInput()
            ? old('additional_contacts', [])
            : $savedAdditionalContacts;
        $nextAdditionalContactIndex = $additionalContacts === [] ? 0 : max(array_keys($additionalContacts)) + 1;

        foreach ($additionalContacts as $index => $contact) {
            $additionalContacts[$index] = self::additionalContactFormData($contact, $index);
        }

        $individualFields = ['name', 'nickname', 'cpf', 'rg'];

        $companyFields = [
            'cnpj',
            'corporate_name',
            'trade_name',
            'state_registration_indicator',
            'state_registration',
            'municipal_registration',
            'tax_regime',
            'cnpj_status',
        ];

        $type = $person instanceof Individual ? 'individual' : 'company';
        $type = old('type', $type);

        $fields = match ($type) {
            'individual' => $individualFields,
            'company' => $companyFields,
        };

        $defaults = array_fill_keys([
            ...$individualFields,
            ...$companyFields,
        ], null);

        return [
            ...$defaults,
            ...($person?->only($fields) ?? []),
            'type' => $type,
            'primary_contact' => $contactValues,
            'primary_contact_templates' => $contactTemplates,
            'additional_contacts' => $additionalContacts,
            'additional_contact_template' => self::additionalContactFormData([], '__CONTACT_INDEX__'),
            'next_additional_contact_index' => $nextAdditionalContactIndex,
            'next_contact_indexes' => $nextContactIndexes,
            'active' => $supplier?->active ?? 1,
            'observation' => json_decode($supplier?->observation ?? '{}', true) ?? [],
        ];
    }

    /**
     * @param  array<string, mixed>  $contact
     * @return array<string, mixed>
     */
    private static function additionalContactFormData(array $contact, int|string $index): array
    {
        foreach (['name' => 'Nome', 'company' => 'Empresa', 'position' => 'Cargo'] as $field => $label) {
            $contact['fields'][$field] = [
                'label' => $label,
                'value' => $contact[$field] ?? '',
                'error_key' => "additional_contacts.{$index}.{$field}",
                'required' => $field === 'name',
                'maxlength' => $field === 'position' ? 120 : 255,
            ];
        }

        foreach (['phones' => 'Telefone', 'emails' => 'E-mail'] as $kind => $label) {
            $emptyRow = ['value' => '', 'label' => ''];
            $rows = $contact[$kind] ?? [$emptyRow];
            $group = [
                'label' => $label,
                'container_id' => "additional-{$index}-{$kind}",
                'next_index' => $rows === [] ? 0 : max(array_keys($rows)) + 1,
                'button_label' => $kind === 'phones' ? 'telefone' : 'e-mail',
                'required' => true,
                'rows' => [],
            ];
            $labels = $kind === 'phones'
                ? ['Celular', 'Comercial', 'Residencial', 'Outro']
                : ['Pessoal', 'Comercial', 'Financeiro', 'Outro'];

            foreach ([...array_keys($rows), '__INDEX__'] as $methodIndex) {
                $methodData = [
                    'index' => $index,
                    'kind' => $kind,
                    'label' => $label,
                    'methodIndex' => $methodIndex,
                    'method' => $methodIndex === '__INDEX__' ? $emptyRow : $rows[$methodIndex],
                    'methodForm' => [
                        'value_field' => "additional_contacts.{$index}.{$kind}.{$methodIndex}.value",
                        'label_field' => "additional_contacts.{$index}.{$kind}.{$methodIndex}.label",
                        'input_id' => "additional-{$index}-{$kind}-{$methodIndex}",
                        'labels' => $labels,
                    ],
                ];

                if ($methodIndex === '__INDEX__') {
                    $group['template'] = $methodData;
                } else {
                    $group['rows'][$methodIndex] = $methodData;
                }
            }

            $contact['method_groups'][$kind] = $group;
        }

        return $contact;
    }
}
