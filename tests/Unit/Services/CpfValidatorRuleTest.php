<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\Rules\CpfValidatorRule;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CpfValidatorRuleTest extends TestCase
{
    /** @return array<string, array{string, bool}> */
    public static function cpfValidationCases(): array
    {
        return [
            'accepts valid cpf' => ['12345678909', true],
            'rejects formatted cpf' => ['123.456.789-09', false],
            'rejects repeated digits' => ['00000000000', false],
            'rejects invalid first check digit' => ['12345678919', false],
        ];
    }

    #[DataProvider('cpfValidationCases')]
    public function test_returns_expected_validation_result(string $cpf, bool $expected): void
    {
        $validator = new CpfValidatorRule();

        $isValid = $validator->isValid($cpf);

        $this->assertSame($expected, $isValid);
    }
}
