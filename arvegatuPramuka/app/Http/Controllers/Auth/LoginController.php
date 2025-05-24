<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function __invoke(Request $request)
    {
        $credentials = $request->only('username', 'password');
        Auth::attempt($credentials);
        if(Auth::attempt($credentials)) {
            $user = Auth::user();
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
                    return redirect()->route('orang-tua.dashboard');
                default:
                    return redirect()->route('/404');
            }
        }
        // Jika autentikasi gagal
        return redirect()->back()->withErrors([
            'login' => 'Username atau password salah.',
        ])->withInput($request->only('username'));
    }
}
