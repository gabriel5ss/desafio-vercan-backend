<?php

declare(strict_types=1);

namespace Modules\Supplier\Presenters;

use Coderflex\LaravelPresenter\Presenter;
use Modules\Supplier\Models\Contact;
use Modules\Supplier\Models\ContactMethod;
use Modules\Supplier\Models\Individual;
use Modules\Supplier\Models\Supplier;

class SupplierPresenter extends Presenter
{
    /**
     * Prepara os dados do fornecedor para preencher o formulário.
     *
     * @return array<string, mixed>
     */
    public function formData(): array
    {
        /** @var Supplier $supplier */
        $supplier = $this->model;

        // Preserva os valores padrão do formulário de criação.
        $supplier = $supplier->exists ? $supplier : null;

        $supplier?->loadMissing(['supplierable', 'contacts.methods']);

        $person = $supplier?->supplierable;
        $primaryContact = $supplier?->contacts
            ->firstWhere('is_primary', true);

        $contactValues = ['id' => session()->getOldInput('primary_contact.id', $primaryContact?->id)];
        $contactTemplates = [];
        $nextContactIndexes = [];
        $emptyRow = [['value' => '', 'label' => '']];

        foreach (['phones' => 'phone', 'emails' => 'email'] as $field => $kind) {
            $savedValues = $primaryContact?->methods
                ->where('kind', $kind)
                ->map(fn (ContactMethod $method): array => [
                    'id' => $method->id,
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
                    ...$contact->only(['id', 'name', 'company', 'position']),
                    'phones' => $contact->methods->where('kind', 'phone')->values()->map->only(['id', 'value', 'label'])->all(),
                    'emails' => $contact->methods->where('kind', 'email')->values()->map->only(['id', 'value', 'label'])->all(),
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

        $default_type = $person instanceof Individual ? 'individual' : 'company';
        $old_type = old('type', $default_type);
        // Retoma o tipo salvo quando a validação devolve um valor inválido ao formulário.
        $type = in_array($old_type, ['individual', 'company'], true) ? $old_type : $default_type;

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
            'active' => $supplier->active ?? 1,
            'observation' => json_decode($supplier->observation ?? '{}', true) ?? [],
        ];
    }

    /**
     * @param array<string, mixed> $contact
     *
     * @return array<string, mixed>
     */
    private function additionalContactFormData(array $contact, int|string $index): array
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
