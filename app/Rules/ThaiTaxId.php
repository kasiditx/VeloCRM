<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ThaiTaxId implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $digits = preg_replace('/\D/', '', (string) $value);

        if ($digits === null || strlen($digits) !== 13) {
            $fail(__('The tax ID must contain exactly 13 digits.'));

            return;
        }

        $sum = 0;
        for ($index = 0; $index < 12; $index++) {
            $sum += (int) $digits[$index] * (13 - $index);
        }

        $checkDigit = (11 - ($sum % 11)) % 10;
        if ($checkDigit !== (int) $digits[12]) {
            $fail(__('The tax ID checksum is invalid.'));
        }
    }
}
