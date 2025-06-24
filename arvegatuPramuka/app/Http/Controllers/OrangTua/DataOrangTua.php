<?php

namespace App\Http\Controllers\OrangTua;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\OrangTua;
use App\Models\Siswa;

class DataOrangTua extends Controller
{
    public function showProfileForm()
    {
        $user = Auth::user();
        $orangTua = OrangTua::where('user_id', $user->id)->with('siswa')->first();
        $loggedInUsername = $user->username;

        // Ambil SEMUA data siswa untuk dropdown
        $siswas = Siswa::all();

        // Pass $orangTua, $loggedInUsername, dan $siswas ke view
        return view('orang_tua.profile.form', compact('orangTua', 'loggedInUsername', 'siswas'));
    }

    public function saveOrUpdateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'nama' => 'required|string|max:255',
            'no_hp' => 'required|string|max:20',
            'alamat' => 'required|string|max:255',
            'siswa_id' => 'nullable|exists:siswas,id', // Validasi siswa_id harus ada di tabel siswas
        ]);

        $orangTua = OrangTua::where('user_id', $user->id)->first();

        if ($orangTua) {
            // Data sudah ada, perbarui
            $orangTua->update([
                'nama' => $request->nama,
                'no_hp' => $request->no_hp,
                'alamat' => $request->alamat,
                'siswa_id' => $request->siswa_id, // Simpan siswa_id yang dipilih
            ]);
            return redirect()->back()->with('success', 'Profil orang tua berhasil diperbarui!');
        } else {
            // Data belum ada, buat baru
            OrangTua::create([
                'nama' => $request->nama,
                'no_hp' => $request->no_hp,
                'alamat' => $request->alamat,
                'siswa_id' => $request->siswa_id, // Simpan siswa_id yang dipilih
                'user_id' => $user->id,
            ]);
            return redirect()->back()->with('success', 'Profil orang tua berhasil disimpan!');
        }
    }
}
