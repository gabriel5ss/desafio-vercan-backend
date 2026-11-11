<?php

declare(strict_types=1);

namespace Modules\Supplier\Tests\Unit\Http\Requests;

use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Modules\Supplier\Http\Requests\SupplierIndexRequest;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SupplierIndexRequestTest extends TestCase
{
    protected SupplierIndexRequest $request;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->request = new SupplierIndexRequest();
    }

    /** @return array<string, array<mixed>> */
    public static function sortingCases(): array
    {
        $error = ['order.0.column' => ['A coluna selecionada não permite ordenação.']];

        return [
            'sortable column' => [
                ['order' => [['column' => 0, 'dir' => 'asc']]],
                [],
            ],
            'without sorting' => [[], []],
            'column without name' => [
                ['order' => [['column' => 0, 'dir' => 'asc']], 'columns' => [0 => ['name' => null]]],
                $error,
            ],
            'edit column with name' => [
                ['order' => [['column' => 4, 'dir' => 'asc']], 'columns' => [4 => ['name' => 'name']]],
                $error,
            ],
            'nonexistent column' => [
                ['order' => [['column' => 5, 'dir' => 'asc']]],
                $error,
            ],
        ];
    }

    public function test_authorize(): void
    {
        $this->assertTrue($this->request->authorize());
    }

    public function test_rules(): void
    {
        $rules = [
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

        $this->assertEquals($rules, $this->request->rules());
    }

    /**
     * @param array<string, mixed> $changes
     * @param array<string, list<string>> $expectedErrors
     */
    #[DataProvider('sortingCases')]
    public function test_after(array $changes, array $expectedErrors): void
    {
        $data = array_replace_recursive([
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'columns' => [
                ['data' => 'name', 'name' => 'name'],
                ['data' => 'trade_name', 'name' => 'trade_name'],
                ['data' => 'document', 'name' => 'document'],
                ['data' => 'active', 'name' => 'active'],
                ['data' => 'edit_url'],
            ],
        ], $changes);

        $validator = new Validator(new Translator(new ArrayLoader(), 'en'), $data, $this->request->rules());
        $validator->after($this->request->after());

        $validator->passes();

        $this->assertSame($expectedErrors, $validator->errors()->toArray());
    }

    public function test_after_failed(): void
    {
        $validator = new Validator(new Translator(new ArrayLoader(), 'en'), [], []);
        $validator->errors()->add('draw', 'Invalid draw.');

        $callback = $this->request->after()[0];
        $callback($validator);

        $this->assertSame(['draw' => ['Invalid draw.']], $validator->errors()->toArray());
    }
}
