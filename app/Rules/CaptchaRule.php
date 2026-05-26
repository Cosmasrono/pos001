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
            // If no secret key is configured, skip validation (for development)
            return;
        }

        try {
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $secretKey,
                'response' => $value,
            ]);

            $data = $response->json();

            if (!$response->successful() || !($data['success'] ?? false)) {
                $fail('Captcha verification failed. Please try again.');
            }

            // Check score if using reCAPTCHA v3
            if (isset($data['score']) && $data['score'] < 0.5) {
                $fail('We detected unusual activity. Please try again.');
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Captcha verification error: ' . $e->getMessage());
            $fail('Unable to verify captcha. Please try again.');
        }
    }
}
