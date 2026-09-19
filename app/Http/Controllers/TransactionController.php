<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Transaction;
use App\Models\Categorie;
use App\Actions\TransactionAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
  private function getHeaderJSandCSS()
  {
    return [
      '<script src="' . asset('assets/js/transaksi.js') . '?v=' . version_assets() . '"></script>',
    ];
  }

  public function index(Request $request)
  {
    $bulan  = (int) request('bulan', now()->month);
    $tahun  = (int) request('tahun', now()->year);
    $search = request('search');

    $query = Expense::byMonth($tahun, $bulan)->orderByDesc('recorded_at');

    if ($search) {
      $query->where(function ($q) use ($search) {
        $q->where('keterangan', 'ilike', "%{$search}%")
          ->orWhere('kategori', 'ilike', "%{$search}%")
          ->orWhere('wa_number', 'like', "%{$search}%");
      });
    }

    $transaksi = $query->paginate(20)->withQueryString();

    $params = ['transaksi', 'bulan', 'tahun', 'search'];

    return view('transaksi.index', compact($params));
  }

  public function hapusTransaksi(Request $request, TransactionAction $action)
  {


    $result = $action->hapusTransaksi($request);

    return response()->json($result);
  }

  public function bboxTransaksi()
  {

    $kategori = Categorie::getCategories()->get();

    $params = ['kategori'];


    return view('transaksi.bbox_transaksi', compact($params));
  }

  public function submitTransaksi(Request $request, TransactionAction $action)
  {
    $result = $action->simpanTransaksi($request);

    // Kembalikan hasilnya sebagai JSON ke jQuery
    return response()->json($result);
  }
}
