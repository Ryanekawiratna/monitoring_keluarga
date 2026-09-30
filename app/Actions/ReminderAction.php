<?php

namespace App\Actions;

use App\Models\Expense;
use App\Models\Reminder;
use App\Models\ReminderPaymentHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ReminderAction
{
  /**
   * Simpan atau update reminder dari form Web
   */
  public function simpanReminder(Request $request): array
  {
    $result = ['is_valid' => false, 'message' => ''];
    $user   = Auth::user();
    $nomorUser = $request->wa_number ?: ($user->nomor_hp ?? $user->nomor_telepon ?? '');

    $validated = $request->validate([
      'id'                  => 'nullable|integer',
      'nama_tagihan'        => 'required|string|max:100',
      'jenis_transaksi'     => 'required|in:keluar,masuk',
      'total_nominal'       => 'required|numeric|min:1',
      'tanggal_jatuh_tempo' => 'required|date',
      'wa_number'           => 'nullable|string|max:20',
      'kategori'            => 'required|string|max:50',
      'perulangan'          => 'required|in:tidak,mingguan,bulanan,tahunan',
      'keterangan'          => 'nullable|string',
    ]);

    try {
      if (!empty($validated['id'])) {
        $reminder = Reminder::findOrFail($validated['id']);

        // Jika total nominal berubah, sesuaikan sisa tagihan secara proporsional
        $selisih = $validated['total_nominal'] - $reminder->total_nominal;
        $sisaBaru = max(0, $reminder->sisa_tagihan + $selisih);

        $reminder->update([
          'nama_tagihan'        => $validated['nama_tagihan'],
          'jenis_transaksi'     => $validated['jenis_transaksi'],
          'total_nominal'       => $validated['total_nominal'],
          'sisa_tagihan'        => $sisaBaru,
          'tanggal_jatuh_tempo' => $validated['tanggal_jatuh_tempo'],
          'wa_number'           => $nomorUser,
          'kategori'            => $validated['kategori'],
          'perulangan'          => $validated['perulangan'],
          'keterangan'          => $validated['keterangan'] ?? null,
          'status'              => $sisaBaru <= 0 ? 'lunas' : 'aktif',
        ]);
        $result['message'] = 'Tagihan berhasil diperbarui';
      } else {
        Reminder::create([
          'user_id'             => $user?->id,
          'wa_number'           => $nomorUser,
          'nama_tagihan'        => $validated['nama_tagihan'],
          'jenis_transaksi'     => $validated['jenis_transaksi'],
          'total_nominal'       => $validated['total_nominal'],
          'sisa_tagihan'        => $validated['total_nominal'],
          'tanggal_jatuh_tempo' => $validated['tanggal_jatuh_tempo'],
          'kategori'            => $validated['kategori'],
          'perulangan'          => $validated['perulangan'],
          'keterangan'          => $validated['keterangan'] ?? null,
          'status'              => 'aktif',
        ]);
        $result['message'] = 'Tagihan berhasil ditambahkan';
      }

      $result['is_valid'] = true;
    } catch (\Throwable $e) {
      $result['is_valid'] = false;
      $result['message'] = $e->getMessage();
    }

    return $result;
  }

  /**
   * Eksekusi Pembayaran Tagihan (Lunas / Cicilan)
   * Terintegrasi langsung dengan tabel `expenses` & `reminder_payment_histories`
   */
  public function bayarTagihan(
    int|Reminder $reminder,
    int $nominalBayar,
    ?string $jatuhTempoBaru = null,
    string $sumber = 'web',
    ?string $catatan = null
  ): array {
    $result = ['is_valid' => false, 'message' => ''];

    if (!($reminder instanceof Reminder)) {
      $reminder = Reminder::find($reminder);
    }

    if (!$reminder) {
      $result['message'] = 'Data tagihan tidak ditemukan.';
      return $result;
    }

    if ($reminder->status === 'lunas' || $reminder->sisa_tagihan <= 0) {
      $result['message'] = 'Tagihan ini sudah lunas sebelumnya.';
      return $result;
    }

    if ($nominalBayar <= 0) {
      $result['message'] = 'Nominal pembayaran harus lebih besar dari 0.';
      return $result;
    }

    try {
      DB::beginTransaction();

      $nominalBayar = min($nominalBayar, $reminder->sisa_tagihan);
      $sisaTagihanBaru = $reminder->sisa_tagihan - $nominalBayar;
      $isLunas = $sisaTagihanBaru <= 0;

      // 1. Simpan Transaksi ke tabel expenses (Laporan Keuangan)
      $keteranganTransaksi = "Pembayaran: {$reminder->nama_tagihan}" . ($isLunas ? ' (Lunas)' : ' (Cicilan)');
      if ($catatan) {
        $keteranganTransaksi .= " - {$catatan}";
      }

      $expense = Expense::create([
        'wa_number'       => $reminder->wa_number,
        'keterangan'      => $keteranganTransaksi,
        'kategori'        => $reminder->kategori,
        'nominal'         => $nominalBayar,
        'jenis_transaksi' => $reminder->jenis_transaksi,
        'recorded_at'     => now(),
      ]);

      // 2. Simpan Riwayat Pembayaran ke reminder_payment_histories
      $history = ReminderPaymentHistory::create([
        'reminder_id'   => $reminder->id,
        'expense_id'    => $expense->id,
        'nominal_bayar' => $nominalBayar,
        'tanggal_bayar' => now(),
        'sumber'        => $sumber,
        'catatan'       => $catatan,
      ]);

      // 3. Update status & sisa tagihan pada reminder
      $updateData = [
        'sisa_tagihan' => $sisaTagihanBaru,
        'status'       => $isLunas ? 'lunas' : 'aktif',
      ];

      // Jika ada tanggal jatuh tempo baru yang diberikan (hanya untuk tagihan tidak berulang / sekali bayar).
      // Tagihan berulang (mingguan/bulanan/tahunan) periode berikutnya HANYA dibuat setelah tagihan saat ini lunas.
      if (!$isLunas && !empty($jatuhTempoBaru) && $reminder->perulangan === 'tidak') {
        $updateData['tanggal_jatuh_tempo'] = Carbon::parse($jatuhTempoBaru)->format('Y-m-d');
      }

      $reminder->update($updateData);

      // 4. Jika lunas & bertipe perulangan (bulanan/tahunan/mingguan),
      // langsung otomatis INSERT record baru untuk periode berikutnya
      if ($isLunas && $reminder->perulangan !== 'tidak') {
        $currentDue = Carbon::parse($reminder->tanggal_jatuh_tempo);
        $nextDueDate = match ($reminder->perulangan) {
          'mingguan' => $currentDue->copy()->addWeek(),
          'bulanan'  => $currentDue->copy()->addMonth(),
          'tahunan'  => $currentDue->copy()->addYear(),
          default    => null,
        };

        if ($nextDueDate) {
          Reminder::create([
            'user_id'             => $reminder->user_id,
            'wa_number'           => $reminder->wa_number,
            'nama_tagihan'        => $reminder->nama_tagihan,
            'jenis_transaksi'     => $reminder->jenis_transaksi,
            'total_nominal'       => $reminder->total_nominal,
            'sisa_tagihan'        => $reminder->total_nominal,
            'tanggal_jatuh_tempo' => $nextDueDate->format('Y-m-d'),
            'kategori'            => $reminder->kategori,
            'perulangan'          => $reminder->perulangan,
            'keterangan'          => $reminder->keterangan,
            'status'              => 'aktif',
          ]);
        }
      }

      DB::commit();

      $result['is_valid']           = true;
      $result['is_lunas']           = $isLunas;
      $result['nominal_bayar']      = $nominalBayar;
      $result['sisa_tagihan']       = $sisaTagihanBaru;
      $result['jatuh_tempo']        = $reminder->tanggal_jatuh_tempo ? $reminder->tanggal_jatuh_tempo->format('d/m/Y') : null;
      $result['jatuh_tempo_baru']   = (!$isLunas && !empty($jatuhTempoBaru) && $reminder->perulangan === 'tidak')
                                        ? $reminder->tanggal_jatuh_tempo?->format('d/m/Y')
                                        : null;
      $result['reminder']           = $reminder;
      $result['message']            = $isLunas ? 'Tagihan berhasil dilunasi!' : 'Pembayaran cicilan berhasil dicatat.';
    } catch (\Throwable $e) {
      DB::rollBack();
      $result['is_valid'] = false;
      $result['message'] = $e->getMessage();
    }

    return $result;
  }

  /**
   * Hapus reminder
   */
  public function hapusReminder(Request $request): array
  {
    $result = ['is_valid' => false, 'message' => ''];
    try {
      $reminder = Reminder::find($request->id_reminder);
      if ($reminder) {
        $reminder->delete();
        $result['is_valid'] = true;
        $result['message'] = 'Data reminder berhasil dihapus.';
      } else {
        $result['message'] = 'Data tidak ditemukan.';
      }
    } catch (\Throwable $e) {
      $result['message'] = $e->getMessage();
    }
    return $result;
  }

  /**
   * Toggle Pause / Non-aktifkan Reminder agar tidak mengirim trigger WA
   */
  public function toggleStatus(Request $request): array
  {
    $result = ['is_valid' => false, 'message' => ''];
    try {
      $reminder = Reminder::findOrFail($request->id_reminder);
      if ($reminder->status === 'nonaktif') {
        $reminder->status = $reminder->sisa_tagihan <= 0 ? 'lunas' : 'aktif';
        $result['message'] = 'Reminder diaktifkan kembali.';
      } else {
        $reminder->status = 'nonaktif';
        $result['message'] = 'Reminder berhasil dinonaktifkan (notifikasi dijeda).';
      }
      $reminder->save();
      $result['is_valid'] = true;
      $result['status']   = $reminder->status;
    } catch (\Throwable $e) {
      $result['message'] = $e->getMessage();
    }
    return $result;
  }
}
