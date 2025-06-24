<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException; // Import ini untuk error handling yang lebih baik

class LoginController extends Controller
{
    public function __invoke(Request $request)
    {
        // 1. Validasi input
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // 2. Coba autentikasi
        $credentials = $request->only('username', 'password');
        if (Auth::attempt($credentials, $request->boolean('remember'))) { // Tambahkan $request->boolean('remember') jika Anda punya checkbox "Remember Me"
            $request->session()->regenerate(); // Regenerasi sesi untuk mencegah session fixation attacks

            $user = Auth::user();

            // 3. Redirect berdasarkan role
            switch ($user->role){
                case 'admin':
                    return redirect()->route('admin.dashboard');
                case 'pembina':
                    return redirect()->route('pembina.dashboard');
                case 'guru':
                    return redirect()->route('guru.dashboard');
                case 'siswa' :
                    return redirect()->route('siswa.dashboard');
                case 'orang_tua' :
                    // Bagian ini telah disederhanakan: Tidak lagi memeriksa relasi orangTua atau status
                    return redirect()->route('orang_tua.dashboard');
                case 'alumni' :
                    return redirect()->route('alumni.dashboard');
                default:
                    // Jika role tidak dikenal atau tidak ada rute yang cocok, logout dan berikan error
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    throw ValidationException::withMessages([
                        'login' => 'Peran pengguna tidak valid. Silakan hubungi admin.',
                    ]);
            }
        }

        // 4. Jika autentikasi gagal
        throw ValidationException::withMessages([
            'login' => 'Username atau password salah.',
        ]);
    }
}
