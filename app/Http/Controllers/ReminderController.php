<?php

namespace App\Http\Controllers;

use App\Models\Reminder;
use App\Models\Categorie;
use App\Actions\ReminderAction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReminderController extends Controller
{
  public function index(Request $request)
  {
    $status = $request->get('status', 'aktif');
    $search = $request->get('search');

    $query = Reminder::with('payments')->orderBy('tanggal_jatuh_tempo', 'asc');

    if ($status === 'aktif') {
      $query->whereIn('status', ['aktif', 'overdue']);
    } elseif ($status && $status !== 'semua') {
      $query->where('status', $status);
    }

    if ($search) {
      $query->where(function ($q) use ($search) {
        $q->where('nama_tagihan', 'ilike', "%{$search}%")
          ->orWhere('kategori', 'ilike', "%{$search}%")
          ->orWhere('wa_number', 'like', "%{$search}%");
      });
    }

    $reminders = $query->paginate(15)->withQueryString();

    // Ringkasan Statistik
    $totalSisaTagihanAktif = Reminder::whereIn('status', ['aktif', 'overdue'])->sum('sisa_tagihan');
    $jumlahTagihanSegera   = Reminder::dueSoon(7)->count();
    $jumlahTagihanOverdue  = Reminder::where('status', 'overdue')->orWhere(function ($q) {
      $q->where('status', 'aktif')->where('tanggal_jatuh_tempo', '<', today());
    })->count();

    $params = [
      'reminders',
      'status',
      'search',
      'totalSisaTagihanAktif',
      'jumlahTagihanSegera',
      'jumlahTagihanOverdue',
    ];

    return view('reminder.index', compact($params));
  }

  public function bboxReminder(Request $request)
  {
    $reminder = null;
    if ($request->filled('id')) {
      $reminder = Reminder::find($request->id);
    }

    $kategori = Categorie::getCategories()->get();
    $user = auth()->user();
    $defaultWaNumber = $reminder ? $reminder->wa_number : ($user->nomor_hp ?? '');

    $params = ['reminder', 'kategori', 'defaultWaNumber'];
    return view('reminder.bbox_reminder', compact($params));
  }

  public function bboxBayar(Request $request)
  {
    $reminder = Reminder::with('payments')->findOrFail($request->id);
    $params = ['reminder'];
    return view('reminder.bbox_bayar', compact($params));
  }

  public function submitReminder(Request $request, ReminderAction $action)
  {
    $result = $action->simpanReminder($request);
    return response()->json($result);
  }

  public function submitBayar(Request $request, ReminderAction $action)
  {
    $request->validate([
      'id_reminder'      => 'required|integer',
      'nominal_bayar'    => 'required|numeric|min:1',
      'jatuh_tempo_baru' => 'nullable|date',
      'catatan'          => 'nullable|string|max:255',
    ]);

    $result = $action->bayarTagihan(
      (int) $request->id_reminder,
      (int) $request->nominal_bayar,
      $request->jatuh_tempo_baru,
      'web',
      $request->catatan
    );

    return response()->json($result);
  }

  public function hapusReminder(Request $request, ReminderAction $action)
  {
    $result = $action->hapusReminder($request);
    return response()->json($result);
  }

  public function toggleStatus(Request $request, ReminderAction $action)
  {
    $result = $action->toggleStatus($request);
    return response()->json($result);
  }
}
