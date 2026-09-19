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
    // 1. Membuat tabel users
    Schema::create('users', function (Blueprint $table) {
      $table->id();
      $table->string('nama');
      $table->string('email')->unique();
      $table->string('nomor_hp')->unique();
      $table->string('password');
      $table->rememberToken();
      $table->timestamps();
    });

    // 2. Membuat tabel password_reset_tokens (Bawaan wajib Laravel)
    Schema::create('password_reset_tokens', function (Blueprint $table) {
      $table->string('email')->primary();
      $table->string('token');
      $table->timestamp('created_at')->nullable();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('users');
  }
};
