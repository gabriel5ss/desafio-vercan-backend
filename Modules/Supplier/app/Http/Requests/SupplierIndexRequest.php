<?php

declare(strict_types=1);

namespace Modules\Supplier\Http\Requests;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SupplierIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'draw' => ['required', 'integer', 'min:0'],
            'start' => ['required', 'integer', 'min:0'],
            'length' => ['required', 'integer', 'min:1', 'max:100'],
            'search' => ['sometimes', 'array'],
            'search.value' => ['sometimes', 'nullable', 'string', 'max:255'],
            'search.regex' => ['sometimes', Rule::in(['false', '0', 0, false])],
            'order' => ['sometimes', 'array', 'list', 'max:1'],
            'order.*.column' => ['required', 'integer', 'min:0'],
            'order.*.dir' => ['required', Rule::in(['asc', 'desc'])],
            'columns' => ['required', 'array', 'list', 'size:5'],
            'columns.*.data' => ['required', Rule::in(['name', 'trade_name', 'document', 'active', 'edit_url'])],
            'columns.*.name' => ['sometimes', 'nullable', Rule::in(['name', 'trade_name', 'document', 'active'])],
            'columns.*.search.value' => ['sometimes', 'nullable', 'string', 'max:0'],
        ];
    }

    /** @return array<Closure(Validator): void> */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $filters = $validator->validated();

                foreach ($filters['order'] ?? [] as $index => $order) {
                    $column = $filters['columns'][(int) $order['column']] ?? null;

                    if (empty($column['name']) || $column['data'] === 'edit_url') {
                        $validator->errors()->add(
                            "order.{$index}.column",
                            'A coluna selecionada não permite ordenação.',
                        );
                    }
                }
            },
        ];
    }
}
