<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\Rules\CnpjValidatorRule;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CnpjValidatorRuleTest extends TestCase
{
    /** @return array<string, array{string, bool}> */
    public static function cnpjValidationCases(): array
    {
        return [
            'accepts valid cnpj' => ['04252011000110', true],
            'rejects formatted cnpj' => ['04.252.011/0001-10', false],
            'rejects repeated digits' => ['00000000000000', false],
            'rejects invalid first check digit' => ['04252011000120', false],
        ];
    }

    #[DataProvider('cnpjValidationCases')]
    public function test_returns_expected_validation_result(string $cnpj, bool $expected): void
    {
        $validator = new CnpjValidatorRule();

        $isValid = $validator->isValid($cnpj);

        $this->assertSame($expected, $isValid);
    }
}
