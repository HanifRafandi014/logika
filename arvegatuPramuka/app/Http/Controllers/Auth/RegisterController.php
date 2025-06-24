<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\OrangTua; // Pastikan Anda sudah mengimpor model OrangTua Anda

class RegisterController extends Controller
{
    public function __invoke(Request $request)
    {
        // 1. Validasi input untuk User
        $request->validate([
            'username' => 'required|string|max:255|unique:users', // Pastikan username unik
            'password' => 'required|string|min:8|confirmed', // Tambahkan `confirmed` jika ada password_confirmation
            'role' => 'required|string|in:admin,pembina,guru,siswa,orang_tua,alumni',
        ]);

        // 2. Buat entri User
        $user = User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        // 3. Jika role adalah 'orang_tua', buat entri di tabel orang_tuas
        if ($user->role === 'orang_tua') {
            // Tentukan status default. Sesuai dengan enum di migrasi, defaultnya 'Anggota'.
            $defaultStatus = 'Anggota';

            // Ambil data opsional dari request jika ada, jika tidak gunakan default/null
            $nama = $request->input('nama');
            $no_hp = $request->input('no_hp');
            $alamat = $request->input('alamat');

            // Buat entri OrangTua yang berelasi dengan User yang baru dibuat
            OrangTua::create([
                'user_id' => $user->id, // Ini adalah kunci penghubung ke tabel users
                'nama' => $nama,
                'no_hp' => $no_hp,
                'alamat' => $alamat,
                'status' => $defaultStatus,
            ]);
        }

        // 4. Redirect ke halaman login dengan pesan sukses
        return redirect()->route('login')->with('success', 'Akun berhasil dibuat. Silakan masuk.');
    }
}
