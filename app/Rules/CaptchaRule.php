<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;

class CaptchaRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            $fail('The captcha verification is required.');
            return;
        }

        // Get reCAPTCHA secret key from environment
        $secretKey = config('services.recaptcha.secret_key') ?? env('RECAPTCHA_SECRET_KEY');
        
        if (empty($secretKey)) {
            return;
        }

        // Skip captcha on local development
        if (app()->environment('local')) {
            return;
        }

        try {
            $response = Http::timeout(5)->asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret'   => $secretKey,
                'response' => $value,
            ]);

            $data = $response->json();

            if (!$response->successful() || !($data['success'] ?? false)) {
                $fail('Captcha verification failed. Please try again.');
                return;
            }

            // Check score for reCAPTCHA v3
            if (isset($data['score']) && $data['score'] < 0.5) {
                $fail('We detected unusual activity. Please try again.');
            }
        } catch (\Exception $e) {
            // Google's API is unreachable — log and fail open so users aren't locked out
            \Illuminate\Support\Facades\Log::warning('Captcha verification skipped (API unreachable): ' . $e->getMessage());
        }
    }
}
