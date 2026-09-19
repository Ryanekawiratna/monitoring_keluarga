<?php

namespace App\Actions;

use App\Models\Expense;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TransactionAction
{
  /**
   * Logika untuk menghapus data transaksi/expense
   */
  public function hapusTransaksi(Request $request)
  {
    $result['is_valid'] = false;

    try {
      // Ambil id_transaksi dari request AJAX
      $idTransaksi = $request->id_transaksi;

      // Cari data berdasarkan ID, lalu hapus
      $expense = Expense::find($idTransaksi);

      if ($expense) {
        $expense->delete();
        $result['is_valid'] = true;
      } else {
        $result['message'] = 'Data transaksi tidak ditemukan.';
      }
    } catch (\Throwable $e) {
      $result['is_valid'] = false;
      $result['message'] = $e->getMessage();
    }
    return $result;
  }


  public function simpanTransaksi(Request $request)
  {

    $result['is_valid'] = false;
    $user = Auth::user();
    $nomorUser = $user->nomor_hp ?? $user->nomor_telepon;

    $validated = $request->validate([
      'tanggal'         => 'required|date',
      'keterangan'      => 'required|string|max:100',
      'kategori'        => 'required|string',
      'nominal'         => 'required|numeric',
      'jenis_transaksi' => 'required|in:masuk,keluar',
    ]);

    try {
      $expense = Expense::create([
        'wa_number'       => $nomorUser,
        'keterangan'      => $validated['keterangan'],
        'kategori'        => $validated['kategori'],
        'nominal'         => $validated['nominal'],
        'jenis_transaksi' => $validated['jenis_transaksi'],
        'recorded_at'     => $validated['tanggal'] . ' ' . now()->format('H:i:s'),
      ]);

      $result['is_valid'] = true;
    } catch (\Throwable $e) {
      $result['is_valid'] = false;
    }

    return $result;
  }
}
