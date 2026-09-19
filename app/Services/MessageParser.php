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
    'food'         => 'Makanan',
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
    'drink'        => 'Minuman',
    'drinks'       => 'Minuman',
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
    'bills'        => 'Tagihan',
    'bill'         => 'Tagihan',
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
    'health'       => 'Kesehatan',
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
    'edu'          => 'Pendidikan',
    'education'    => 'Pendidikan',
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
    'entertain'    => 'Hiburan',
    'healing'      => 'Hiburan',
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
    'gaji'        => 'Gaji',
    'salary'      => 'Gaji',
    'upah'        => 'Gaji',
    'freelance'   => 'Freelance',
    'proyek'      => 'Freelance',
    'project'     => 'Freelance',
    'bonus'       => 'Bonus',
    'thr'         => 'Bonus',
    'insentif'    => 'Bonus',
    'komisi'      => 'Komisi',
    'investasi'   => 'Investasi',
    'dividen'     => 'Dividen',
    'saham'       => 'Investasi',
    'crypto'      => 'Investasi',
    'kripto'      => 'Investasi',
    'hadiah'      => 'Hadiah',
    'cashback'    => 'Cashback',
    'omset'       => 'Pendapatan',
    'omzet'       => 'Pendapatan',
    'penjualan'   => 'Penjualan Barang',
    'jual'        => 'Penjualan Barang',
    'masuk'       => 'Pendapatan',
    'pemasukan'   => 'Pendapatan',
    'dana masuk'  => 'Pendapatan',
    'transfer'    => 'Pendapatan',
    'cair'        => 'Pendapatan',
    'piutang'     => 'Pengembalian Dana',
    'tunjangan'   => 'Tunjangan',
    'royalti'     => 'Royalti',
    'lainnya'     => 'Lainnya',
  ];

  private const EXPLICIT_CATEGORY_TAGS = [
    'food',
    'makanan',
    'mkn',
    'minum',
    'minuman',
    'drink',
    'drinks',
    'transport',
    'transportasi',
    'kendaraan',
    'bills',
    'bill',
    'tagihan',
    'rutin',
    'shopping',
    'belanja',
    'rumah',
    'rumahtangga',
    'health',
    'kesehatan',
    'obat',
    'pendidikan',
    'edu',
    'education',
    'sekolah',
    'hiburan',
    'entertain',
    'healing',
    'pakaian',
    'fashion',
    'anak',
    'kids',
    'baby',
    'hewan',
    'pets',
    'donasi',
    'amal',
    'sedekah',
    'pajak',
    'tax',
    'bank',
    'admin',
    'other',
    'lainnya',
    'lain',
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
    $trimmed    = trim($text);
    $normalized = mb_strtolower($trimmed);

    // ── 1. Cek command keyword ────────────────────────────────────────
    foreach (self::COMMANDS as $keyword => $intent) {
      if ($normalized === $keyword) {
        return ['type' => 'command', 'intent' => $intent, 'raw' => $raw];
      }
    }

    // ── 2. Pemasukan ──────────────────────────────────────────────────
    // Mendukung: keyword (optional deskripsi) nominal (multiplier)
    // Contoh: "gaji 5000000", "gaji kantor 5000000", "transfer ayah 500rb", "masuk 100rb"
    $pattern_pemasukan = '/^(masuk|gaji|freelance|bonus|pemasukan|omset|omzet|transfer|cair|dana masuk|penjualan|dividen|hadiah|cashback|thr|tunjangan)(?:\s+(.+?))?\s+([\d.,]+)\s*(rb|ribu|k|jt|juta|m|miliar)?\s*$/iu';
    if (preg_match($pattern_pemasukan, $trimmed, $hasil)) {
      $keyword = mb_strtolower(trim($hasil[1]));
      $extra   = isset($hasil[2]) ? trim($hasil[2]) : '';
      $nominal = $this->parseNominal($hasil[3], mb_strtolower(trim($hasil[4] ?? '')));

      if ($nominal !== null && $nominal > 0) {
        $kategori = self::KATEGORI_PEMASUKAN[$keyword] ?? 'Pendapatan';
        if ($extra !== '') {
          $keterangan = ucwords(mb_strtolower($keyword . ' ' . $extra));
        } else {
          $keterangan = ucwords(mb_strtolower($keyword));
        }

        return [
          'type' => 'transaction',
          'data' => [
            'keterangan'      => $keterangan,
            'kategori'        => $kategori,
            'nominal'         => $nominal,
            'jenis_transaksi' => 'masuk',
          ],
          'raw' => $raw,
        ];
      }
    }

    // ── 3. Pengeluaran ────────────────────────────────────────────────
    // Pola yang didukung:
    //   "makan food 50000"   (3 kata: keterangan + kategori eksplisit + nominal)
    //   "makan 50000"        (2 kata: keterangan/kategori + nominal)
    //   "beli kopi 15k"      (multi kata + nominal)
    //   "nasi padang 25000"  (multi kata tanpa tag kategori)
    $pattern_pengeluaran = '/^(.+?)\s+([\d.,]+)\s*(rb|ribu|k|jt|juta|m|miliar)?\s*$/iu';
    if (preg_match($pattern_pengeluaran, $trimmed, $m)) {
      $textBefore = trim($m[1]);
      $nominal    = $this->parseNominal($m[2], mb_strtolower(trim($m[3] ?? '')));

      if ($nominal !== null && $nominal > 0) {
        $words     = preg_split('/\s+/', $textBefore);
        $wordCount = count($words);
        $lastWord  = mb_strtolower(end($words));

        $kategori   = null;
        $keterangan = null;

        // Cek jika kata terakhir adalah tag kategori eksplisit (misal: "makan food 50000")
        if ($wordCount >= 2 && in_array($lastWord, self::EXPLICIT_CATEGORY_TAGS, true)) {
          $kategori = self::KATEGORI_ALIAS[$lastWord] ?? ucwords($lastWord);
          $keteranganWords = array_slice($words, 0, -1);
          $keterangan = ucwords(mb_strtolower(implode(' ', $keteranganWords)));
        } else {
          // Cari kategori dari kata-kata yang ada di keterangan (periksa dari belakang ke depan)
          $keterangan = ucwords(mb_strtolower($textBefore));
          for ($i = $wordCount - 1; $i >= 0; $i--) {
            $w = mb_strtolower($words[$i]);
            if (isset(self::KATEGORI_ALIAS[$w])) {
              $kategori = self::KATEGORI_ALIAS[$w];
              break;
            }
          }

          if (!$kategori) {
            $kategori = 'Lainnya';
          }
        }

        return [
          'type' => 'transaction',
          'data' => [
            'keterangan'      => $keterangan,
            'kategori'        => $kategori,
            'nominal'         => $nominal,
            'jenis_transaksi' => 'keluar',
          ],
          'raw' => $raw,
        ];
      }
    }

    // ── 4. Tidak dikenali → serahkan ke GeminiService ────────────────
    return ['type' => 'unknown', 'raw' => $raw];
  }

  /**
   * Helper parsing nominal dan multiplier (rb, k, jt, dst)
   */
  private function parseNominal(string $nominalRaw, string $multiplierKey): ?int
  {
    if ($multiplierKey !== '') {
      $nominalValue = str_replace(',', '.', $nominalRaw);
      if (!is_numeric($nominalValue)) {
        return null;
      }
      $nominal = (float) $nominalValue;
    } else {
      $nominalClean = preg_replace('/[.,]/', '', $nominalRaw);
      if (!ctype_digit($nominalClean)) {
        return null;
      }
      $nominal = (int) $nominalClean;
    }

    if ($nominal <= 0) {
      return null;
    }

    $nominal = match ($multiplierKey) {
      'rb', 'ribu', 'k' => $nominal * 1_000,
      'jt', 'juta'      => $nominal * 1_000_000,
      'm', 'miliar'     => $nominal * 1_000_000_000,
      default           => $nominal,
    };

    return (int) round($nominal);
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

    $jenisTransaksi = ($aiData['jenis_transaksi'] ?? '') === 'masuk' ? 'masuk' : 'keluar';
    $kategoriRaw    = mb_strtolower(trim($aiData['kategori'] ?? 'other'));

    if ($jenisTransaksi === 'masuk') {
      $kategori = self::KATEGORI_PEMASUKAN[$kategoriRaw] ?? self::KATEGORI_ALIAS[$kategoriRaw] ?? ucwords($kategoriRaw);
    } else {
      $kategori = self::KATEGORI_ALIAS[$kategoriRaw] ?? ucwords($kategoriRaw);
    }

    return [
      'type' => 'transaction',
      'data' => [
        'keterangan'      => ucwords(mb_strtolower(trim($aiData['keterangan'] ?? ($jenisTransaksi === 'masuk' ? 'Pemasukan' : 'Pengeluaran')))),
        'kategori'        => $kategori,
        'nominal'         => $nominal,
        'jenis_transaksi' => $jenisTransaksi,
      ],
      'raw' => $raw,
    ];
  }
}
