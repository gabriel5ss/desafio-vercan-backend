<?php

declare(strict_types=1);

namespace Modules\Supplier\Services\Actions;

use Modules\Supplier\Models\Contact;
use Modules\Supplier\Models\Supplier;

final class SupplierContactAction
{
    /** @param array<string, mixed> $data */
    public function handle(Supplier $supplier, array $data, bool $isPrimary): Contact
    {
        $contacts = $supplier->contacts()->where('is_primary', $isPrimary);
        $contact = ! empty($data['id'])
            ? $contacts->findOrFail($data['id'])
            : ($isPrimary ? $contacts->firstOrNew() : $contacts->make());

        $contact->fill([
            'is_primary' => $isPrimary,
            'name' => $isPrimary ? null : $data['name'],
            'company' => $isPrimary ? null : ($data['company'] ?? null),
            'position' => $isPrimary ? null : ($data['position'] ?? null),
        ])->save();

        foreach (['phones' => 'phone', 'emails' => 'email'] as $field => $kind) {
            $methodIds = array_map(function (array $method) use ($contact, $kind): int {
                $methods = $contact->methods()->where('kind', $kind);
                $contactMethod = ! empty($method['id'])
                    ? $methods->findOrFail($method['id'])
                    : $methods->make();

                $contactMethod->fill([
                    'kind' => $kind,
                    'value' => $method['value'],
                    'label' => $method['label'] ?? null,
                ])->save();

                return $contactMethod->id;
            }, $data[$field] ?? []);

            $contact->methods()->where('kind', $kind)->whereNotIn('id', $methodIds)->delete();
        }

        return $contact;
    }
}
