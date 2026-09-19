<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KirimDevService
{
    private string $apiKey;
    private string $phoneNumberId;
    private string $baseUrl = 'https://api.kirimdev.com/v1';

    public function __construct()
    {
        $this->apiKey        = config('services.kirimdev.api_key');
        $this->phoneNumberId = config('services.kirimdev.phone_number_id');
    }

    /**
     * Kirim pesan teks ke nomor WA
     * KirimDev kompatibel dengan Meta Cloud API — payload sama persis
     */
    public function sendText(string $to, string $message): bool
    {
        // Pastikan format nomor benar: 628xxx (tanpa + di awal)
        $number = ltrim($to, '+');

        try {
            $response = Http::withToken($this->apiKey)
                ->post("{$this->baseUrl}/{$this->phoneNumberId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'recipient_type'    => 'individual',
                    'to'                => $number,
                    'type'              => 'text',
                    'text'              => [
                        'preview_url' => false,
                        'body'        => $message,
                    ],
                ]);

            if (!$response->successful()) {
                Log::error('KirimDev send failed', [
                    'to'     => $number,
                    'status' => $response->status(),
                    'body'   => $response->json(),
                ]);
                return false;
            }

            Log::info('KirimDev message sent', ['to' => $number]);
            return true;

        } catch (\Exception $e) {
            Log::error('KirimDev exception', [
                'to'      => $to,
                'message' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
