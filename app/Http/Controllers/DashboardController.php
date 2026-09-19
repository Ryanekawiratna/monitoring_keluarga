<?php

namespace App\Http\Controllers;

use App\Actions\DashboardAction;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
  public function index(Request $request, DashboardAction $action)
  {
    // 1. Ambil input filter dari user
    $bulan = (int) request('bulan', now()->month);
    $tahun = (int) request('tahun', now()->year);

    // 2. Ambil semua data statistik dari Action
    $data = $action->getDashboardData($bulan, $tahun);

    // 3. Sisipkan juga variabel $bulan dan $tahun ke dalam array $data agar bisa dipakai di View
    $data['bulan'] = $bulan;
    $data['tahun'] = $tahun;

    // 4. Kirim semua data ke View
    return view('dashboard.index', $data);
  }
}
