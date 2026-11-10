<?php

declare(strict_types=1);

namespace Modules\Supplier\Services\Actions;

use Modules\Supplier\Models\Supplier;

final class SupplierContactAction
{
    /** @param array<string, mixed> $data */
    public function handle(Supplier $supplier, array $data, bool $isPrimary): void
    {
        $contact = $supplier->contacts()->create([
            'is_primary' => $isPrimary,
            'name' => $isPrimary ? null : $data['name'],
            'company' => $isPrimary ? null : ($data['company'] ?? null),
            'position' => $isPrimary ? null : ($data['position'] ?? null),
        ]);

        foreach (['phones' => 'phone', 'emails' => 'email'] as $field => $kind) {
            $contact->methods()->createMany(array_map(
                fn (array $method): array => [
                    'kind' => $kind,
                    'value' => $method['value'],
                    'label' => $method['label'] ?? null,
                ],
                $data[$field] ?? [],
            ));
        }
    }
}
