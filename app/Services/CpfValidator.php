<?php

namespace App\Services;

class CpfValidator
{
    public function isValid(string $cpf): bool
    {
        if (! preg_match('/^[0-9]{11}$/', $cpf)) {
            return false;
        }

        if (preg_match('/^([0-9])\1{10}$/', $cpf)) {
            return false;
        }

        for ($length = 9; $length <= 10; $length++) {
            $sum = 0;

            for ($index = 0; $index < $length; $index++) {
                $sum += (int) $cpf[$index] * ($length + 1 - $index);
            }

            $digit = ($sum * 10) % 11;

            if ($digit === 10) {
                $digit = 0;
            }

            if ((int) $cpf[$length] !== $digit) {
                return false;
            }
        }

        return true;
    }
}
