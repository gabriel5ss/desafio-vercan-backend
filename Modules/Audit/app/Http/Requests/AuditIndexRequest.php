<?php

declare(strict_types=1);

namespace Modules\Audit\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AuditIndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
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
            'order.*.column' => ['required', 'integer', 'min:0', 'max:5'],
            'order.*.dir' => ['required', Rule::in(['asc', 'desc'])],
            'columns' => ['required', 'array', 'list', 'size:8'],
            'columns.*.data' => ['required', 'in:created_at,user_name,event,auditable_type,auditable_id,ip_address,old_values,new_values'],
            'columns.*.name' => ['sometimes', 'nullable', 'in:created_at,user_name,event,auditable_type,auditable_id,ip_address,old_values,new_values'],
            'columns.*.search.value' => ['sometimes', 'nullable', 'string', 'max:0'],
        ];
    }
}
