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
    Schema::create('reminder_payment_histories', function (Blueprint $table) {
      $table->id();
      $table->foreignId('reminder_id')->constrained('reminders')->cascadeOnDelete();
      $table->foreignId('expense_id')->nullable()->constrained('expenses')->nullOnDelete();
      $table->unsignedBigInteger('nominal_bayar');
      $table->timestamp('tanggal_bayar')->useCurrent();
      $table->enum('sumber', ['whatsapp', 'web'])->default('web');
      $table->string('catatan')->nullable();
      $table->timestamps();

      $table->index(['reminder_id', 'tanggal_bayar']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('reminder_payment_histories');
  }
};
