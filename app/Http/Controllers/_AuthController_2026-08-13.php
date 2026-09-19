<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Session::get('dashboard_auth')) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $validUsername = config('services.dashboard.username');
        $validPassword = config('services.dashboard.password');

        if (
            $request->username === $validUsername &&
            $request->password === $validPassword
        ) {
            Session::put('dashboard_auth', true);
            return redirect()->route('dashboard');
        }

        return back()->withErrors(['login' => 'Username atau password salah.']);
    }

    public function logout()
    {
        Session::forget('dashboard_auth');
        return redirect()->route('login');
    }
}
