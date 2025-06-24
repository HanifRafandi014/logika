<?php

namespace App\Http\Controllers\Alumni;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Alumni;
use App\Models\User;

class ProfilAlumniController extends Controller
{
    public function index()
    {
        // Ambil user yang sedang login
        $user = Auth::user();

        // Eager load relasi 'siswa' untuk mendapatkan nama siswa
        $alumni = Alumni::where('user_id', $user->id)->with('siswa')->first();

        if (!$alumni) {
            // Jika data alumni tidak ditemukan, mungkin karena belum terdaftar
            // Redirect ke halaman lain atau tampilkan pesan error yang lebih informatif
            return redirect()->back()->with('error', 'Profil alumni Anda belum terdaftar. Silakan hubungi administrator.');
        }

        return view('alumni.profil_alumni', compact('alumni', 'user'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'tahun_lulus' => 'required|integer', // Tambahkan validasi tipe data
            'pekerjaan' => 'required|string|max:255',
            'no_hp' => 'required|string|max:20', // Sesuaikan dengan panjang kolom di DB
        ]);

        $user = Auth::user();
        $alumni = Alumni::where('user_id', $user->id)->first();

        if (!$alumni) {
            return redirect()->back()->with('error', 'Data alumni tidak ditemukan.');
        }

        // Simpan perubahan hanya untuk kolom yang diizinkan dari request
        // Kolom 'nama' tidak diikutsertakan karena seharusnya diambil dari relasi siswa
        $alumni->update($request->only(['tahun_lulus', 'pekerjaan', 'no_hp']));

        return redirect()->back()->with('success', 'Profil berhasil diperbarui.');
    }
}
