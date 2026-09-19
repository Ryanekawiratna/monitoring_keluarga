<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * GeminiService — Fallback parser menggunakan Google Gemini Flash.
 *
 * Dipakai ketika MessageParser (regex) tidak bisa mengenali format pesan.
 * Output: array dengan key type, data (jika transaksi), atau intent (jika command).
 */
class GeminiService
{
  private string $apiKey;
  private string $model;
  private string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models';

  public function __construct()
  {
    $this->apiKey = config('services.gemini.api_key');
    $this->model  = config('services.gemini.model', 'gemini-2.0-flash');
  }

  /**
   * Minta Gemini untuk mengekstrak intent/transaksi dari pesan bebas.
   *
   * Return shape sama dengan MessageParser::parse():
   *   ['type' => 'transaction', 'data' => [...], 'raw' => string]
   *   ['type' => 'command',     'intent' => string, 'raw' => string]
   *   ['type' => 'unknown',     'raw' => string]
   *
   * @return array{type: string, data?: array, intent?: string, raw: string}
   */
  public function parseMessage(string $text): array
  {
    $prompt = $this->buildPrompt($text);

    try {
      $response = Http::timeout(10)
        ->post("{$this->baseUrl}/{$this->model}:generateContent?key={$this->apiKey}", [
          'contents' => [
            [
              'parts' => [
                ['text' => $prompt],
              ],
            ],
          ],
          'generationConfig' => [
            'temperature'     => 0.1,
            'maxOutputTokens' => 256,
            'responseMimeType' => 'application/json',
          ],
        ]);

      if (!$response->successful()) {
        Log::error('Gemini API error', [
          'status' => $response->status(),
          'body'   => $response->json(),
        ]);
        return ['type' => 'unknown', 'raw' => $text];
      }

      $responseData = $response->json();
      $rawText      = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? '';

      Log::info('Gemini raw response', ['text' => $rawText]);

      $parsed = json_decode(trim($rawText), true);

      if (!is_array($parsed)) {
        Log::warning('Gemini returned non-JSON', ['raw' => $rawText]);
        return ['type' => 'unknown', 'raw' => $text];
      }

      return $this->mapGeminiResponse($parsed, $text);
    } catch (\Exception $e) {
      Log::error('GeminiService exception', [
        'message' => $e->getMessage(),
        'text'    => $text,
      ]);
      return ['type' => 'unknown', 'raw' => $text];
    }
  }

  // ─── Private ────────────────────────────────────────────────────────

  private function buildPrompt(string $text): string
  {
    return <<<PROMPT
          Kamu adalah parser pesan untuk aplikasi pencatat keuangan WhatsApp (Bahasa Indonesia).

          Tugasmu: ekstrak informasi dari pesan dan kembalikan HANYA JSON, tanpa teks lain.

          SCHEMA JSON yang valid:
          1. Transaksi (pengeluaran atau pemasukan):
            {"type":"transaction","keterangan":"<deskripsi singkat>","kategori":"<kategori>","nominal":<angka integer>,"jenis_transaksi":"keluar"|"masuk"}

          2. Command:
            {"type":"command","intent":"<intent>"}
            Intent yang valid: rekap_harian | rekap_mingguan | rekap_bulanan | hapus_terakhir | bantuan

          3. Tidak dikenali:
            {"type":"unknown"}

          ATURAN:
          - nominal selalu integer (contoh: "50rb" = 50000, "1.5jt" = 1500000, "dua puluh ribu" = 20000)
          - jenis_transaksi: "keluar" untuk pengeluaran/belanja/bayar, "masuk" untuk pemasukan/gaji/transfer masuk/bonus/pendapatan
          - kategori pengeluaran: food | transport | bills | shopping | health | other
          - kategori pemasukan: gaji | freelance | bonus | investasi | other
          - keterangan singkat (maks 40 karakter), Title Case, Bahasa Indonesia
          - Jika ragu antara transaksi dan unknown, pilih transaksi jika ada nominal yang bisa diekstrak

          PESAN:
          {$text}

          JSON:
          PROMPT;
  }

  /**
   * Map respons Gemini ke format standar MessageParser.
   */
  private function mapGeminiResponse(array $data, string $raw): array
  {
    $type = $data['type'] ?? 'unknown';

    if ($type === 'transaction') {
      $nominal = (int) ($data['nominal'] ?? 0);
      if ($nominal <= 0) {
        return ['type' => 'unknown', 'raw' => $raw];
      }

      $jenisTransaksi = ($data['jenis_transaksi'] ?? '') === 'masuk' ? 'masuk' : 'keluar';

      return [
        'type' => 'transaction',
        'data' => [
          'keterangan'      => ucwords(mb_strtolower(trim($data['keterangan'] ?? ($jenisTransaksi === 'masuk' ? 'Pemasukan' : 'Pengeluaran')))),
          'kategori'        => mb_strtolower(trim($data['kategori'] ?? 'other')),
          'nominal'         => $nominal,
          'jenis_transaksi' => $jenisTransaksi,
        ],
        'raw' => $raw,
      ];
    }

    if ($type === 'command') {
      $validIntents = [
        'rekap_harian',
        'rekap_mingguan',
        'rekap_bulanan',
        'hapus_terakhir',
        'bantuan',
      ];
      $intent = $data['intent'] ?? '';

      if (in_array($intent, $validIntents, true)) {
        return ['type' => 'command', 'intent' => $intent, 'raw' => $raw];
      }
    }

    return ['type' => 'unknown', 'raw' => $raw];
  }
}
