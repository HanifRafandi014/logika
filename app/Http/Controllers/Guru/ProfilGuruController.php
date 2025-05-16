<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Guru;
use App\Models\User;

class ProfilGuruController extends Controller
{
    public function index()
    {
        $user = Auth::user(); // ambil user yang login
        $guru = Guru::where('user_id', $user->id)->first();

        if (!$guru) {
            return redirect()->back()->with('error', 'Data guru tidak ditemukan.');
        }

        return view('guru.profil_guru', compact('guru', 'user'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'nama' => 'nullable',
            'mata_pelajaran' => 'nullable',
            'nip' => 'nullable',
            'pembina_pramuka' => 'nullable|boolean',
        ]);

        $user = Auth::user();
        $guru = Guru::where('user_id', $user->id)->first();

        if (!$guru) {
            return redirect()->back()->with('error', 'Data guru tidak ditemukan.');
        }

        // Simpan perubahan
        $guru->update($request->all());

        return redirect()->back()->with('success', 'Profil berhasil diperbarui.');
    }
}
