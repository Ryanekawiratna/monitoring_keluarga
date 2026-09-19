<?php

namespace App\Services;

class WebhookVerifier
{
    private const TOLERANCE_SECONDS = 300; // 5 menit

    /**
     * Verifikasi HMAC-SHA256 signature dari KirimDev
     *
     * Header format: X-Kirim-Signature: t=1716480000,v1=<hex>
     * Signed payload: "{t}.{raw_body}"
     */
    public function verify(string $rawBody, ?string $signatureHeader): bool
    {
        if (!$signatureHeader) {
            return false;
        }

        $secret = config('services.kirimdev.webhook_secret');
        if (!$secret) {
            return false;
        }

        // Parse header: t=... dan v1=...
        $parts = explode(',', $signatureHeader);
        $t     = null;
        $v1s   = [];

        foreach ($parts as $part) {
            $part = trim($part);
            if (str_starts_with($part, 't=')) {
                $t = (int) substr($part, 2);
            } elseif (str_starts_with($part, 'v1=')) {
                $v1s[] = substr($part, 3);
            }
        }

        if (!$t || empty($v1s)) {
            return false;
        }

        // Replay protection — tolak signature lebih dari 5 menit
        if (abs(time() - $t) > self::TOLERANCE_SECONDS) {
            return false;
        }

        // Hitung expected HMAC
        $signed   = "{$t}.{$rawBody}";
        $expected = hash_hmac('sha256', $signed, $secret);

        // Bandingkan dengan constant-time compare
        foreach ($v1s as $received) {
            if (hash_equals($expected, $received)) {
                return true;
            }
        }

        return false;
    }
}
