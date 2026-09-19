<?php

namespace Database\Seeders;

use App\Models\Categorie;
use Illuminate\Database\Seeder;

class CategorieSeeder extends Seeder
{
  public function run(): void
  {
    $categories = [
      // ─── Kategori Pengeluaran Umum ───────────────────
      ['nama_kategori' => 'Makanan', 'jenis' => 'keluar'],
      ['nama_kategori' => 'Minuman', 'jenis' => 'keluar'],
      ['nama_kategori' => 'Transportasi', 'jenis' => 'keluar'],
      ['nama_kategori' => 'Tagihan', 'jenis' => 'keluar'],
      ['nama_kategori' => 'Belanja', 'jenis' => 'keluar'],
      ['nama_kategori' => 'Rumah Tangga', 'jenis' => 'keluar'],
      ['nama_kategori' => 'Kesehatan', 'jenis' => 'keluar'],
      ['nama_kategori' => 'Pendidikan', 'jenis' => 'keluar'],
      ['nama_kategori' => 'Hiburan', 'jenis' => 'keluar'],
      ['nama_kategori' => 'Pakaian', 'jenis' => 'keluar'],
      ['nama_kategori' => 'Anak', 'jenis' => 'keluar'],
      ['nama_kategori' => 'Hewan', 'jenis' => 'keluar'],
      ['nama_kategori' => 'Donasi', 'jenis' => 'keluar'],
      ['nama_kategori' => 'Pajak', 'jenis' => 'keluar'],
      ['nama_kategori' => 'Biaya Bank', 'jenis' => 'keluar'],

      // Tambahan Pengeluaran Umum Lainnya
      ['nama_kategori' => 'Hutang', 'jenis' => 'keluar'],
      ['nama_kategori' => 'Pinjaman', 'jenis' => 'keluar'],
      ['nama_kategori' => 'Asuransi', 'jenis' => 'keluar'],
      ['nama_kategori' => 'Olahraga', 'jenis' => 'keluar'],
      ['nama_kategori' => 'Kecantikan', 'jenis' => 'keluar'],
      ['nama_kategori' => 'Subscription', 'jenis' => 'keluar'],
      ['nama_kategori' => 'Pulsa', 'jenis' => 'keluar'],
      ['nama_kategori' => 'Internet', 'jenis' => 'keluar'],
      ['nama_kategori' => 'Listrik', 'jenis' => 'keluar'],
      ['nama_kategori' => 'Air', 'jenis' => 'keluar'],
      ['nama_kategori' => 'Bensin', 'jenis' => 'keluar'],
      ['nama_kategori' => 'Parkir', 'jenis' => 'keluar'],
      ['nama_kategori' => 'Tol', 'jenis' => 'keluar'],
      ['nama_kategori' => 'Servis Kendaraan', 'jenis' => 'keluar'],
      ['nama_kategori' => 'Sewa Kos', 'jenis' => 'keluar'],
      ['nama_kategori' => 'Perawatan Rumah', 'jenis' => 'keluar'],
      ['nama_kategori' => 'Liburan', 'jenis' => 'keluar'],
      ['nama_kategori' => 'Hobi', 'jenis' => 'keluar'],

      // ─── Kategori Pemasukan Umum ─────────────────────
      ['nama_kategori' => 'Pendapatan', 'jenis' => 'masuk'],
      ['nama_kategori' => 'Investasi', 'jenis' => 'masuk'],

      // Tambahan Pemasukan Umum Lainnya
      ['nama_kategori' => 'Freelance', 'jenis' => 'masuk'],
      ['nama_kategori' => 'Bonus', 'jenis' => 'masuk'],
      ['nama_kategori' => 'Hadiah', 'jenis' => 'masuk'],
      ['nama_kategori' => 'Penjualan Barang', 'jenis' => 'masuk'],
      ['nama_kategori' => 'Cashback', 'jenis' => 'masuk'],
      ['nama_kategori' => 'Dividen', 'jenis' => 'masuk'],
      ['nama_kategori' => 'Hutang Masuk', 'jenis' => 'masuk'],
      ['nama_kategori' => 'Gaji', 'jenis' => 'masuk'],
      ['nama_kategori' => 'Tunjangan', 'jenis' => 'masuk'],
      ['nama_kategori' => 'Komisi', 'jenis' => 'masuk'],
      ['nama_kategori' => 'Royalti', 'jenis' => 'masuk'],
      ['nama_kategori' => 'Pengembalian Dana', 'jenis' => 'masuk'],

      // ─── Kategori Umum Netral/Lainnya ─────────────────
      ['nama_kategori' => 'Lainnya', 'jenis' => 'keluar'],
      ['nama_kategori' => 'Lainnya', 'jenis' => 'masuk'],
    ];

    foreach ($categories as $category) {
      Categorie::firstOrCreate(
        ['nama_kategori' => $category['nama_kategori']],
        ['jenis' => $category['jenis']]
      );
    }
  }
}
