<?php

declare(strict_types=1);

namespace App\Services\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CpfValidatorRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->isValid($value)) {
            $fail('O CPF informado é inválido.');
        }
    }

    public function isValid(string $cpf): bool
    {
        if (! $this->formatCheck($cpf)) {
            return false;
        }

        for ($length = 9; $length <= 10; $length++) {
            $sum = 0;

            for ($index = 0; $index < $length; $index++) {
                $sum += (int) $cpf[$index] * ($length + 1 - $index);
            }

            $digit = ($sum * 10 % 11) % 10;

            if ((int) $cpf[$length] !== $digit) {
                return false;
            }
        }

        return true;
    }

    public function formatCheck(string $cpf): bool
    {
        if (! preg_match('/^[0-9]{11}$/', $cpf)) {
            return false;
        }

        return ! preg_match('/^([0-9])\1{10}$/', $cpf);
    }
}
