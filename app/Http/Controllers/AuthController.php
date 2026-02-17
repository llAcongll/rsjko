<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller as BaseController;

class AuthController extends BaseController
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required',
            'tahun' => 'nullable|integer'
        ]);

        if (!Auth::attempt(['username' => $credentials['username'], 'password' => $credentials['password']])) {
            return response()->json([
                'success' => false,
                'message' => 'Username atau password salah'
            ], 401);
        }

        $request->session()->regenerate();

        // Simpan tahun anggaran ke session
        if ($request->has('tahun')) {
            session(['tahun_anggaran' => $request->tahun]);
        } else {
            // Default ke tahun sekarang jika tidak ada
            session(['tahun_anggaran' => date('Y')]);
        }

        return response()->json([
            'success' => true
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('toast', 'Berhasil logout');
    }

}
