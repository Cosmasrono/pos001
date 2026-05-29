<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ActiveEmail implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $domain = substr(strrchr($value, '@'), 1);

        if (!$domain || !checkdnsrr($domain, 'MX')) {
            $fail('The :attribute domain does not appear to be active or able to receive emails.');
        }
    }
}
