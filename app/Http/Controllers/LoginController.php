<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Usuario;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('login'); // tu vista personalizada en resources/views/login.blade.php
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $email = $credentials['email'];

        // Check if user exists and is active
        $user = Usuario::where('email', $email)->first();

        if (!$user || $user->estado !== 'activo') {
            return back()->with('error', 'Credenciales incorrectas.')->withInput();
        }

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->with('error', 'Credenciales incorrectas.')->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
