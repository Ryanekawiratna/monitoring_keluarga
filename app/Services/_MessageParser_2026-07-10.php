<?php

namespace App\Services;

class MessageParser
{
  /**
   * Command keywords → intent mapping.
   * Urutan penting: lebih spesifik di atas.
   */
  private const COMMANDS = [
    'rekap hari ini'  => 'rekap_harian',
    'rekap minggu ini' => 'rekap_mingguan',
    'rekap bulan ini' => 'rekap_bulanan',
    'rekap minggu'    => 'rekap_mingguan',
    'rekap bulan'     => 'rekap_bulanan',
    'rekap'           => 'rekap_harian',
    'hapus'           => 'hapus_terakhir',
    'bantuan'         => 'bantuan',
    'help'            => 'bantuan',
    'menu'            => 'bantuan',
  ];

  /**
   * Alias kategori umum → canonical name.
   */
  private const KATEGORI_ALIAS = [

    // Makanan
    'mkn'          => 'Makanan',
    'makan'        => 'Makanan',
    'makanan'      => 'Makanan',
    'sarapan'      => 'Makanan',
    'makanpagi'    => 'Makanan',
    'makansiang'   => 'Makanan',
    'makanmalam'   => 'Makanan',
    'snack'        => 'Makanan',
    'cemilan'      => 'Makanan',
    'camilan'      => 'Makanan',
    'jajan'        => 'Makanan',
    'kuliner'      => 'Makanan',
    'warung'       => 'Makanan',
    'kantin'       => 'Makanan',
    'restoran'     => 'Makanan',
    'rumahmakan'   => 'Makanan',
    'bakso'        => 'Makanan',
    'soto'         => 'Makanan',
    'mie'          => 'Makanan',
    'nasi'         => 'Makanan',
    'ayam'         => 'Makanan',
    'ikan'         => 'Makanan',
    'lauk'         => 'Makanan',

    // Minuman
    'minum'        => 'Minuman',
    'minuman'      => 'Minuman',
    'kopi'         => 'Minuman',
    'teh'          => 'Minuman',
    'jus'          => 'Minuman',
    'susu'         => 'Minuman',
    'es'           => 'Minuman',
    'airminum'     => 'Minuman',
    'boba'         => 'Minuman',
    'coklat'       => 'Minuman',

    // Transportasi
    'bbm'          => 'Transportasi',
    'bensin'       => 'Transportasi',
    'solar'        => 'Transportasi',
    'transport'    => 'Transportasi',
    'transportasi' => 'Transportasi',
    'kendaraan'    => 'Transportasi',
    'motor'        => 'Transportasi',
    'mobil'        => 'Transportasi',
    'ojek'         => 'Transportasi',
    'taksi'        => 'Transportasi',
    'bus'          => 'Transportasi',
    'kereta'       => 'Transportasi',
    'angkot'       => 'Transportasi',
    'parkir'       => 'Transportasi',
    'tol'          => 'Transportasi',
    'servis'       => 'Transportasi',
    'service'      => 'Transportasi',
    'bengkel'      => 'Transportasi',
    'oli'          => 'Transportasi',
    'ban'          => 'Transportasi',
    'cuci'         => 'Transportasi',

    // Tagihan
    'tagihan'      => 'Tagihan',
    'listrik'      => 'Tagihan',
    'air'          => 'Tagihan',
    'internet'     => 'Tagihan',
    'wifi'         => 'Tagihan',
    'pulsa'        => 'Tagihan',
    'paketdata'    => 'Tagihan',
    'telepon'      => 'Tagihan',
    'langganan'    => 'Tagihan',
    'subscription' => 'Tagihan',
    'iuran'        => 'Tagihan',
    'cicilan'      => 'Tagihan',
    'angsuran'     => 'Tagihan',
    'sewa'         => 'Tagihan',
    'kontrak'      => 'Tagihan',
    'kos'          => 'Tagihan',

    // Belanja
    'belanja'      => 'Belanja',
    'beli'         => 'Belanja',
    'shopping'     => 'Belanja',
    'sembako'      => 'Belanja',
    'kebutuhan'    => 'Belanja',
    'keperluan'    => 'Belanja',
    'grosir'       => 'Belanja',
    'eceran'       => 'Belanja',
    'peralatan'    => 'Belanja',
    'perlengkapan' => 'Belanja',

    // Rumah Tangga
    'rumah'        => 'Rumah Tangga',
    'dapur'        => 'Rumah Tangga',
    'sabun'        => 'Rumah Tangga',
    'sampo'        => 'Rumah Tangga',
    'detergen'     => 'Rumah Tangga',
    'pel'          => 'Rumah Tangga',
    'sapu'         => 'Rumah Tangga',
    'galon'        => 'Rumah Tangga',
    'tisu'         => 'Rumah Tangga',
    'gas'          => 'Rumah Tangga',
    'elpiji'       => 'Rumah Tangga',
    'perabot'      => 'Rumah Tangga',
    'furnitur'     => 'Rumah Tangga',
    'renovasi'     => 'Rumah Tangga',
    'tukang'       => 'Rumah Tangga',

    // Kesehatan
    'kesehatan'    => 'Kesehatan',
    'obat'         => 'Kesehatan',
    'dokter'       => 'Kesehatan',
    'klinik'       => 'Kesehatan',
    'rumahsakit'   => 'Kesehatan',
    'rawatjalan'   => 'Kesehatan',
    'rawatinap'    => 'Kesehatan',
    'laboratorium' => 'Kesehatan',
    'cekup'        => 'Kesehatan',
    'vaksin'       => 'Kesehatan',
    'vitamin'      => 'Kesehatan',

    // Pendidikan
    'pendidikan'   => 'Pendidikan',
    'sekolah'      => 'Pendidikan',
    'kuliah'       => 'Pendidikan',
    'kampus'       => 'Pendidikan',
    'spp'          => 'Pendidikan',
    'ukt'          => 'Pendidikan',
    'kursus'       => 'Pendidikan',
    'les'          => 'Pendidikan',
    'pelatihan'    => 'Pendidikan',
    'buku'         => 'Pendidikan',
    'modul'        => 'Pendidikan',

    // Hiburan
    'hiburan'      => 'Hiburan',
    'rekreasi'     => 'Hiburan',
    'wisata'       => 'Hiburan',
    'liburan'      => 'Hiburan',
    'jalanjalan'   => 'Hiburan',
    'bioskop'      => 'Hiburan',
    'film'         => 'Hiburan',
    'game'         => 'Hiburan',
    'karaoke'      => 'Hiburan',
    'konser'       => 'Hiburan',

    // Pakaian
    'pakaian'      => 'Pakaian',
    'baju'         => 'Pakaian',
    'celana'       => 'Pakaian',
    'sepatu'       => 'Pakaian',
    'sandal'       => 'Pakaian',
    'jaket'        => 'Pakaian',
    'kemeja'       => 'Pakaian',
    'kaos'         => 'Pakaian',
    'tas'          => 'Pakaian',
    'dompet'       => 'Pakaian',

    // Anak
    'anak'         => 'Anak',
    'bayi'         => 'Anak',
    'popok'        => 'Anak',
    'diapers'      => 'Anak',
    'mpasi'        => 'Anak',
    'mainan'       => 'Anak',
    'susuformula'  => 'Anak',

    // Hewan
    'hewan'        => 'Hewan',
    'peliharaan'   => 'Hewan',
    'kucing'       => 'Hewan',
    'anjing'       => 'Hewan',
    'pakan'        => 'Hewan',
    'grooming'     => 'Hewan',

    // Pendapatan
    'gaji'         => 'Pendapatan',
    'salary'       => 'Pendapatan',
    'upah'         => 'Pendapatan',
    'honor'        => 'Pendapatan',
    'bonus'        => 'Pendapatan',
    'komisi'       => 'Pendapatan',
    'fee'          => 'Pendapatan',
    'pendapatan'   => 'Pendapatan',
    'income'       => 'Pendapatan',
    'insentif'     => 'Pendapatan',
    'thr'          => 'Pendapatan',

    // Investasi
    'investasi'    => 'Investasi',
    'saham'        => 'Investasi',
    'obligasi'     => 'Investasi',
    'deposito'     => 'Investasi',
    'emas'         => 'Investasi',
    'reksadana'    => 'Investasi',
    'kripto'       => 'Investasi',
    'dividen'      => 'Investasi',

    // Donasi
    'donasi'       => 'Donasi',
    'sedekah'      => 'Donasi',
    'zakat'        => 'Donasi',
    'infak'        => 'Donasi',
    'amal'         => 'Donasi',
    'sumbangan'    => 'Donasi',

    // Pajak
    'pajak'        => 'Pajak',
    'ppn'          => 'Pajak',
    'pph'          => 'Pajak',

    // Biaya Bank
    'admin'        => 'Biaya Bank',
    'administrasi' => 'Biaya Bank',
    'materai'      => 'Biaya Bank',
    'transfer'     => 'Biaya Bank',

    // Lainnya
    'lain'         => 'Lainnya',
    'lainnya'      => 'Lainnya',
    'other'        => 'Lainnya',
    'misc'         => 'Lainnya',
  ];


  private const KATEGORI_PEMASUKAN = [
    'gaji'      => 'gaji',
    'freelance' => 'freelance',
    'bonus'     => 'bonus',
    'investasi'     => 'investasi',
  ];

  /**
   * Parse pesan WhatsApp.
   *
   * Return shape:
   *   ['type' => 'command',     'intent' => string,  'raw' => string]
   *   ['type' => 'transaction', 'data'   => array,   'raw' => string]
   *   ['type' => 'unknown',                           'raw' => string]
   *
   * @return array{type: string, data?: array, intent?: string, raw: string}
   */
  public function parse(string $text): array
  {
    $raw        = $text;
    $normalized = mb_strtolower(trim($text));

    // ── 1. Cek command keyword ────────────────────────────────────────
    foreach (self::COMMANDS as $keyword => $intent) {
      if ($normalized === $keyword) {
        return ['type' => 'command', 'intent' => $intent, 'raw' => $raw];
      }
    }

    // ── 2. Regex: nominal di akhir kalimat ───────────────────────────
    //
    // Pola yang didukung (case-insensitive, nominal boleh ada titik/koma):
    //   "makan food 50000"
    //   "makan food 50.000"
    //   "makan food 50,000"
    //   "beli kopi food 15rb"       → rb/ribu/k = ×1000
    //   "listrik bills 250jt"       → jt/juta   = ×1.000.000
    //   "beli kopi food 15k"        → k         = ×1000
    //
    // Keterangan boleh lebih dari satu kata (semua sebelum kategori+nominal).
    // Kategori = satu kata sebelum nominal.

    $pattern = '/^(.+?)\s+(\w+)\s+([\d.,]+)\s*(rb|ribu|k|jt|juta|m|miliar)?\s*$/iu';

    // if (preg_match($pattern, trim($text), $m)) {
    //   $keteranganRaw = trim($m[1]);
    //   $kategoriRaw   = trim($m[2]);
    //   $nominalRaw    = trim($m[3]);
    //   $multiplierKey = mb_strtolower(trim($m[4] ?? ''));

    //   $nominalClean = preg_replace('/[.,]/', '', $nominalRaw);

    //   if (ctype_digit($nominalClean) && (int) $nominalClean > 0) {
    //     $nominal = (int) $nominalClean;

    //     // Terapkan multiplier
    //     $nominal = match ($multiplierKey) {
    //       'rb', 'ribu', 'k' => $nominal * 1_000,
    //       'jt', 'juta'      => $nominal * 1_000_000,
    //       'm', 'miliar'     => $nominal * 1_000_000_000,
    //       default           => $nominal,
    //     };

    //     // Normalkan kategori via alias
    //     $kategoriNorm = mb_strtolower($kategoriRaw);
    //     $kategori     = self::KATEGORI_ALIAS[$kategoriNorm] ?? $kategoriNorm;

    //     return [
    //       'type' => 'transaction',
    //       'data' => [
    //         'keterangan' => ucwords(mb_strtolower($keteranganRaw)),
    //         'kategori'   => $kategori,
    //         'nominal'    => $nominal,
    //       ],
    //       'raw' => $raw,
    //     ];
    //   }
    // }

    echo "<pre>";
    print_r($text);
    die;

    if (preg_match($pattern, trim($text), $m)) {
      $keteranganRaw = trim($m[1]);
      $kategoriRaw   = trim($m[2]);
      $nominalRaw    = trim($m[3]);
      $multiplierKey = mb_strtolower(trim($m[4] ?? ''));

      // Parsing nominal
      if ($multiplierKey !== '') {
        // ada k/rb/jt/m -> titik/koma dianggap desimal
        $nominalValue = str_replace(',', '.', $nominalRaw);

        if (!is_numeric($nominalValue)) {
          return ['type' => 'unknown', 'raw' => $raw];
        }

        $nominal = (float) $nominalValue;
      } else {
        // tidak ada multiplier -> titik/koma dianggap pemisah ribuan
        $nominalClean = preg_replace('/[.,]/', '', $nominalRaw);

        if (!ctype_digit($nominalClean)) {
          return ['type' => 'unknown', 'raw' => $raw];
        }

        $nominal = (int) $nominalClean;
      }

      if ($nominal <= 0) {
        return ['type' => 'unknown', 'raw' => $raw];
      }

      // multiplier
      $nominal = match ($multiplierKey) {
        'rb', 'ribu', 'k' => $nominal * 1_000,
        'jt', 'juta'      => $nominal * 1_000_000,
        'm', 'miliar'     => $nominal * 1_000_000_000,
        default           => $nominal,
      };

      $nominal = (int) round($nominal);

      // Normalkan kategori via alias
      $kategoriNorm = mb_strtolower($kategoriRaw);
      $kategori     = self::KATEGORI_ALIAS[$kategoriNorm] ?? $kategoriNorm;

      return [
        'type' => 'transaction',
        'data' => [
          'keterangan' => ucwords(mb_strtolower($keteranganRaw)),
          'kategori'   => $kategori,
          'nominal'    => $nominal,
        ],
        'raw' => $raw,
      ];
    }

    // ── 3. Tidak dikenali → serahkan ke GeminiService ────────────────
    return ['type' => 'unknown', 'raw' => $raw];
  }

  /**
   * Buat struct transaksi dari hasil parse AI (array JSON dari Gemini).
   * Dipakai oleh ProcessWhatsAppMessage setelah mendapat respons AI.
   *
   * @param array $aiData  Harus punya key: keterangan, kategori, nominal
   * @param string $raw
   * @return array
   */
  public function buildFromAi(array $aiData, string $raw): array
  {
    $nominal = (int) ($aiData['nominal'] ?? 0);

    if ($nominal <= 0) {
      return ['type' => 'unknown', 'raw' => $raw];
    }

    $kategoriRaw  = mb_strtolower(trim($aiData['kategori'] ?? 'other'));
    $kategori     = self::KATEGORI_ALIAS[$kategoriRaw] ?? $kategoriRaw;

    return [
      'type' => 'transaction',
      'data' => [
        'keterangan' => ucwords(mb_strtolower(trim($aiData['keterangan'] ?? 'Lainnya'))),
        'kategori'   => $kategori,
        'nominal'    => $nominal,
      ],
      'raw' => $raw,
    ];
  }
}
