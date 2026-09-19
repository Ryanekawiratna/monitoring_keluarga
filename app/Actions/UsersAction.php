<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UsersAction
{
  /**
   * Logika untuk memproses pendaftaran user baru
   */
  public function registerUser(Request $request)
  {
    // 1. Validasi Input
    $request->validate([
      'nama' => 'required|string|max:100',
      'email' => 'required|email|unique:users,email',
      'nomor_hp' => 'required|numeric|unique:users,nomor_hp',
      'password' => 'required|min:6',
    ]);

    // 2. Simpan Data ke Database dengan Password yang di-Hash
    $user = User::create([
      'nama' => $request->nama,
      'email' => $request->email,
      'nomor_hp' => $request->nomor_hp,
      'password' => Hash::make($request->password),
    ]);

    // 3. Langsung loginkan user
    Auth::login($user);

    return $user;
  }

  /**
   * Logika untuk memproses login user
   */
  public function loginUser(Request $request)
  {
    // 1. Validasi Input form login
    $credentials = $request->validate([
      'email' => 'required|email',
      'password' => 'required',
    ]);

    // 2. Coba melakukan autentikasi
    if (!Auth::attempt($credentials)) {
      throw ValidationException::withMessages([
        'email' => 'Email atau password yang Anda masukkan salah.',
      ]);
    }

    // 3. Regenerasi session demi keamanan
    $request->session()->regenerate();

    return Auth::user();
  }
}
