<?php
// routes/web.php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

// ─── Auth ─────────────────────────────────────────────────────
// Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
// Route::post('/login', [AuthController::class, 'login'])->name('login.post');
// Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Middleware 'guest' untuk pengguna yang BELUM login (Tamu)
Route::middleware('guest')->group(function () {
  Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
  Route::post('/register', [AuthController::class, 'processRegister']);

  Route::get('/', [AuthController::class, 'showLogin'])->name('login');
  Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
  Route::post('/login', [AuthController::class, 'processLogin']);
});

// Middleware 'auth' untuk pengguna yang SUDAH login
Route::middleware('auth')->group(function () {
  // Halaman Dashboard sederhana
  // Route::get('/dashboard', function () {
  //   return view('dashboard'); // Kita akan buat tampilannya nanti
  // })->name('dashboard');
  Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

  Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
  Route::get('/transaksi', [TransactionController::class, 'index'])->name('transaksi');
  Route::post('/transaksi/bbox', [TransactionController::class, 'bboxTransaksi'])->name('transaksi.bbox');
  Route::post('/transaksi/submitTransaksi', [TransactionController::class, 'submitTransaksi'])->name('transaksi.submitTransaksi');
  // Route::delete('/transaksi/{expense}', [TransactionController::class, 'hapus'])->name('transaksi.hapus');
  Route::post('/transaksi/hapusTransaksi', [TransactionController::class, 'hapusTransaksi'])->name('transaksi.hapusTransaksi');
});

// ─── Dashboard (protected) ────────────────────────────────────
Route::middleware('dashboard')->group(function () {});
  // Route::delete('/transaksi/{expense}', [DashboardController::class, 'hapus'])->name('transaksi.hapus');
