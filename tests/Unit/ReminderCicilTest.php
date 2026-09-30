<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Reminder;
use App\Actions\ReminderAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

class ReminderCicilTest extends TestCase
{
  use RefreshDatabase;

  public function test_cicilan_pada_tagihan_berulang_tidak_mengubah_periode_jatuh_tempo()
  {
    $action = app(ReminderAction::class);

    $originalDueDate = Carbon::parse('2026-10-15');

    $reminder = Reminder::create([
      'wa_number'           => '628123456789',
      'nama_tagihan'        => 'Tagihan Wifi Bulanan',
      'jenis_transaksi'     => 'keluar',
      'total_nominal'       => 300000,
      'sisa_tagihan'        => 300000,
      'tanggal_jatuh_tempo' => $originalDueDate->format('Y-m-d'),
      'kategori'            => 'bills',
      'perulangan'          => 'bulanan',
      'status'              => 'aktif',
    ]);

    // Simulasi bayar cicilan sebagian (100.000)
    // Mencoba kirim tanggal baru (misal terkirim jatuhTempoBaru = '2026-11-15')
    $result = $action->bayarTagihan($reminder, 100000, '2026-11-15', 'web', 'Cicilan 1');

    $this->assertTrue($result['is_valid'], $result['message'] ?? '');
    $this->assertFalse($result['is_lunas']);
    $this->assertEquals(200000, $result['sisa_tagihan']);

    $reminder->refresh();

    // Pastikan status masih aktif dan sisa tagihan berkurang
    $this->assertEquals('aktif', $reminder->status);
    $this->assertEquals(200000, $reminder->sisa_tagihan);

    // Tanggal jatuh tempo pada tagihan berjalan TIDAK boleh berubah ke periode berikutnya!
    $this->assertEquals('2026-10-15', $reminder->tanggal_jatuh_tempo->format('Y-m-d'));

    // Tidak boleh ada tagihan baru yang terbuat untuk periode berikutnya sebelum lunas
    $this->assertEquals(1, Reminder::where('nama_tagihan', 'Tagihan Wifi Bulanan')->count());

    // Sekarang lakukan pelunasan sisa 200.000
    $resultPelunasan = $action->bayarTagihan($reminder, 200000, null, 'web', 'Pelunasan');
    $this->assertTrue($resultPelunasan['is_valid']);
    $this->assertTrue($resultPelunasan['is_lunas']);

    $reminder->refresh();
    $this->assertEquals('lunas', $reminder->status);
    $this->assertEquals(0, $reminder->sisa_tagihan);

    // Setelah lunas, tagihan periode berikutnya (2026-11-15) baru otomatis dibuat!
    $this->assertEquals(2, Reminder::where('nama_tagihan', 'Tagihan Wifi Bulanan')->count());

    $nextPeriodReminder = Reminder::where('nama_tagihan', 'Tagihan Wifi Bulanan')
      ->where('status', 'aktif')
      ->first();

    $this->assertNotNull($nextPeriodReminder);
    $this->assertEquals('2026-11-15', $nextPeriodReminder->tanggal_jatuh_tempo->format('Y-m-d'));
    $this->assertEquals(300000, $nextPeriodReminder->sisa_tagihan);
  }

  public function test_cicilan_pada_tagihan_tidak_berulang_bisa_memperbarui_jatuh_tempo()
  {
    $action = app(ReminderAction::class);

    $reminder = Reminder::create([
      'wa_number'           => '628123456789',
      'nama_tagihan'        => 'Pinjaman Teman',
      'jenis_transaksi'     => 'keluar',
      'total_nominal'       => 1000000,
      'sisa_tagihan'        => 1000000,
      'tanggal_jatuh_tempo' => '2026-10-10',
      'kategori'            => 'other',
      'perulangan'          => 'tidak',
      'status'              => 'aktif',
    ]);

    // Cicil dengan perpanjangan jatuh tempo ke 2026-10-25
    $result = $action->bayarTagihan($reminder, 400000, '2026-10-25', 'web', 'Cicilan 1');

    $this->assertTrue($result['is_valid']);
    $this->assertFalse($result['is_lunas']);

    $reminder->refresh();
    $this->assertEquals(600000, $reminder->sisa_tagihan);
    $this->assertEquals('2026-10-25', $reminder->tanggal_jatuh_tempo->format('Y-m-d'));

    // Tidak ada penambahan tagihan baru
    $this->assertEquals(1, Reminder::where('nama_tagihan', 'Pinjaman Teman')->count());
  }
}
