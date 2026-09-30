<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('reminders', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
      $table->string('wa_number', 20)->index();
      $table->string('nama_tagihan', 100);
      $table->enum('jenis_transaksi', ['keluar', 'masuk'])->default('keluar');
      $table->unsignedBigInteger('total_nominal');
      $table->unsignedBigInteger('sisa_tagihan');
      $table->date('tanggal_jatuh_tempo')->index();
      $table->string('kategori', 50)->index();
      $table->enum('perulangan', ['tidak', 'mingguan', 'bulanan', 'tahunan'])->default('tidak');
      $table->text('keterangan')->nullable();
      $table->enum('status', ['aktif', 'lunas', 'overdue', 'nonaktif'])->default('aktif')->index();
      $table->timestamp('last_notified_at')->nullable();
      $table->timestamps();

      $table->index(['wa_number', 'status', 'tanggal_jatuh_tempo']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('reminders');
  }
};
