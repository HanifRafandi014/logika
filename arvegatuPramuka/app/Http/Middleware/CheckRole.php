<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Periksa apakah pengguna sudah login
        if (!Auth::check()) {
            return redirect('/login')->with('error', 'Anda harus login untuk mengakses halaman ini.');
        }

        $user = Auth::user();
        $userRole = $user->role; 
        $allowedRoles = [];
        $allowedStatuses = []; // Variabel ini tetap ada jika diperlukan untuk peran lain

        // Pisahkan parameter roles dan status dari middleware
        foreach ($roles as $param) {
            if (str_starts_with($param, 'status:')) {
                $allowedStatuses[] = substr($param, 7);
            } else {
                $allowedRoles[] = $param;
            }
        }

        // Periksa apakah peran pengguna ada di antara peran yang diizinkan
        if (!in_array($userRole, $allowedRoles)) {
            return redirect('/dashboard')->with('error', 'Anda tidak memiliki peran yang sesuai untuk mengakses halaman ini.');
        }
        return $next($request);
    }
}
