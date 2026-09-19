<?php

namespace App\Services;

class MessageParser
{
  private const COMMANDS = [
    'rekap'           => 'rekap_harian',
    'rekap hari ini'  => 'rekap_harian',
    'rekap minggu'    => 'rekap_mingguan',
    'rekap bulan'     => 'rekap_bulanan',
    'rekap bulan ini' => 'rekap_bulanan',
    'hapus'           => 'hapus_terakhir',
    'bantuan'         => 'bantuan',
    'help'            => 'bantuan',
    'menu'            => 'bantuan',
  ];

  /**
   * @return array{type: string, data?: array, intent?: string, raw: string}
   */
  public function parse(string $text): array
  {
    $normalized = trim(strtolower($text));

    foreach (self::COMMANDS as $keyword => $intent) {
      if ($normalized === $keyword) {
        return ['type' => 'command', 'intent' => $intent, 'raw' => $text];
      }
    }

    // Format: keterangan kategori nominal

    $parts = preg_split('/\s+/', trim($text));

    if (count($parts) < 3) {
      return [
        'type' => 'unknown',
        'raw' => $text
      ];
    }



    $nominalRaw = strtolower(array_pop($parts));
    $kategori   = strtolower(array_pop($parts));
    $keterangan = implode(' ', $parts);

    $nominalClean = $this->generateNominal($nominalRaw);

    if ($nominalClean !== null) {
      return [
        'type' => 'transaction',
        'data' => [
          'keterangan' => ucfirst(strtolower($keterangan)),
          'kategori'   => $kategori,
          'nominal'    => (int) $nominalClean,
        ],
        'raw' => $text,
      ];
    }

    return [
      'type' => 'unknown',
      'raw' => $text,
    ];
  }



  public function generateNominal(string $nominal)
  {
    $value = strtolower(trim($nominal));
    $value = preg_replace('/\s+/', '', $value);

    // debug safety
    if ($value === '') return null;

    // 3k
    if (preg_match('/^(\d+(?:\.\d+)?)k$/', $value, $m)) {
      return (int) round(((float) $m[1]) * 1000);
    }

    // 2jt
    if (preg_match('/^(\d+(?:\.\d+)?)jt$/', $value, $m)) {
      return (int) round(((float) $m[1]) * 1000000);
    }

    // angka biasa
    $value = preg_replace('/[.,]/', '', $value);

    if (ctype_digit($value)) {
      return (int) $value;
    }

    return null;
  }
}
