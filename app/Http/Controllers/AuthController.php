<?php

namespace App\Http\Controllers;

use App\Actions\usersAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
  // --- BAGIAN REGISTER ---
  public function showRegister()
  {
    return view('auth.register');
  }

  public function processRegister(Request $request, usersAction $action)
  {
    // Menjalankan logika register dari Action
    $action->registerUser($request);

    return redirect()->route('dashboard')->with('success', 'Registrasi berhasil!');
  }

  // --- BAGIAN LOGIN ---
  public function showLogin()
  {
    return view('auth.login');
  }

  public function processLogin(Request $request, usersAction $action)
  {
    try {
      // Menjalankan logika login dari Action
      $action->loginUser($request);

      return redirect()->intended('/dashboard');
    } catch (ValidationException $e) {
      // Menangkap pesan error jika login gagal
      return back()->withErrors([
        'email' => 'Email atau password yang Anda masukkan salah.',
      ])->onlyInput('email');
    }
  }

  // --- BAGIAN LOGOUT ---
  public function logout(Request $request)
  {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/login')->with('success', 'Anda berhasil logout!');
  }
}
