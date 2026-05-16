<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private string $token;
    private string $phoneNumberId;
    private string $apiVersion = 'v19.0';

    public function __construct()
    {
        $this->token         = config('services.whatsapp.token', '');
        $this->phoneNumberId = config('services.whatsapp.phone_number_id', '');
    }

    /**
     * Send a plain-text WhatsApp message to a given number.
     * Works within a 24-hour session window (user-initiated) or for business-initiated
     * conversations where the number has messaged your WhatsApp Business account before.
     */
    public function send(string $to, string $message): bool
    {
        if (!$this->token || !$this->phoneNumberId) {
            Log::warning('WhatsApp not configured — WHATSAPP_TOKEN or WHATSAPP_PHONE_NUMBER_ID missing.');
            return false;
        }

        $to = $this->formatNumber($to);

        try {
            $response = Http::withToken($this->token)
                ->timeout(15)
                ->post("https://graph.facebook.com/{$this->apiVersion}/{$this->phoneNumberId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'to'                => $to,
                    'type'              => 'text',
                    'text'              => ['body' => $message],
                ]);

            if ($response->successful()) {
                Log::info('WhatsApp message sent', ['to' => $to]);
                return true;
            }

            Log::error('WhatsApp send failed', [
                'to'     => $to,
                'status' => $response->status(),
                'body'   => $response->json(),
            ]);
            return false;

        } catch (\Throwable $e) {
            Log::error('WhatsApp exception', ['error' => $e->getMessage()]);
            return false;
        }
    }

    private function formatNumber(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);

        if (str_starts_with($phone, '0')) {
            $phone = '254' . substr($phone, 1);
        } elseif (!str_starts_with($phone, '254')) {
            $phone = '254' . $phone;
        }

        return $phone;
    }
}
