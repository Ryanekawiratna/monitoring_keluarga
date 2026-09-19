<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessWhatsAppMessage;
use App\Services\WebhookVerifier;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class WhatsAppController extends Controller
{
  public function __construct(private readonly WebhookVerifier $verifier) {}

  /**
   * POST /api/webhook/whatsapp
   *
   * KirimDev mengirim payload Meta passthrough:
   * Header X-Kirim-Source: meta
   * Header X-Kirim-Event: message.received
   * Header X-Kirim-Signature: t=...,v1=...
   */
  public function handle(Request $request): Response
  {
    // WAJIB: ambil raw body SEBELUM parsing
    // HMAC dihitung dari raw bytes, bukan parsed JSON

    $rawBody   = $request->getContent();
    $signature = $request->header('X-Kirim-Signature');

    // Verifikasi HMAC signature
    if (!$this->verifier->verify($rawBody, $signature)) {
      Log::warning('KirimDev webhook: invalid signature', [
        'ip'        => $request->ip(),
        'signature' => $signature,
      ]);
      return response('Unauthorized', 401);
    }

    $payload = json_decode($rawBody, true);
    $event   = $request->header('X-Kirim-Event');

    Log::info('KirimDev webhook received', ['event' => $event]);

    // Hanya proses event pesan masuk
    if ($event !== 'message.received') {
      return response('ok', 200);
    }

    $this->processMessageReceived($payload);

    return response('ok', 200);
  }

  private function processMessageReceived(array $payload): void
  {
    // KirimDev mengirim Meta passthrough — struktur sama dengan Meta Cloud API
    $entry   = $payload['entry'][0] ?? null;
    $change  = $entry['changes'][0]['value'] ?? null;
    $message = $change['messages'][0] ?? null;

    if (!$message || ($message['type'] ?? '') !== 'text') {
      return;
    }

    $from      = $message['from'];           // nomor pengirim: 628xxx
    $text      = $message['text']['body'];
    $messageId = $message['id'];

    Log::info('WA message to process', compact('from', 'text'));

    // Dispatch ke queue — return 200 secepat mungkin ke KirimDev
    ProcessWhatsAppMessage::dispatch($from, trim($text), $messageId);
  }
}
