<?php

namespace App\Services;

class CnpjValidator
{
    public function isValid(string $cnpj): bool
    {
        if (! preg_match('/^[0-9]{14}$/', $cnpj)) {
            return false;
        }

        if (preg_match('/^([0-9])\1{13}$/', $cnpj)) {
            return false;
        }

        $weightSets = [
            [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2],
            [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2],
        ];

        foreach ($weightSets as $weights) {
            $sum = 0;

            foreach ($weights as $index => $weight) {
                $sum += (int) $cnpj[$index] * $weight;
            }

            $remainder = $sum % 11;
            $digit = $remainder < 2 ? 0 : 11 - $remainder;

            if ((int) $cnpj[count($weights)] !== $digit) {
                return false;
            }
        }

        return true;
    }
}
