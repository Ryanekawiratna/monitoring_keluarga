<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('expenses', function (Blueprint $table) {
      $table->id();
      $table->string('wa_number', 20);
      $table->string('keterangan', 100);
      $table->string('kategori', 50);
      $table->unsignedBigInteger('nominal');
      $table->string('jenis_transaksi', 20)->default('keluar');
      $table->timestamp('recorded_at');
      $table->timestamps();

      $table->index(['wa_number', 'recorded_at']);
      $table->index('kategori');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('expenses');
  }
};
