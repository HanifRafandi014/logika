<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class RegisterController extends Controller
{
    public function __invoke(Request $request)
    {
        // 1. Validasi input untuk User
        $request->validate([
            'username' => 'required|string|max:255|unique:users', // Pastikan username unik
            'password' => 'required|string|', // Tambahkan `confirmed` jika ada password_confirmation
            'role' => 'required|string|in:admin,pembina,guru,siswa,orang_tua,alumni',
        ]);

        // 2. Buat entri User
        $user = User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        // 4. Redirect ke halaman login dengan pesan sukses
        return redirect()->route('login')->with('success', 'Akun berhasil dibuat. Silakan masuk.');
    }
}
