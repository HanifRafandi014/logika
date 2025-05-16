<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Hash; 
use App\Models\User;
use App\Models\Siswa;
use Illuminate\Support\Facades\Storage;

class EditProfilSiswaController extends Controller
{
    public function editProfilSiswa()
    {
        $user = Auth::user();
        $siswa = $user->siswa;
        if(!$siswa){
            $siswa = new Siswa();
        }

        return view('siswa.editProfil-siswa', compact('user', 'siswa'));
    }

    public function updateProfilSiswa(Request $request)
    {
        $request->validate([
            'username' => 'required|string|unique:users,username,' . Auth::id(),
            'password' => 'nullable|string|confirmed',
            'nama' => 'required|string',
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $user = Auth::user();
        $user->username = $request->username;

        if ($request->password) {
            $user->password = Hash::make($request->password);
        }

        if ($request->hasFile('image')) {
            $imagePath = $request->file('foto_profil')->store('images', 'public');
            $user->profile= $imagePath;
        }

        // Jika ada gambar baru yang diunggah
        if ($request->hasFile('foto_profil')) {
            // Menghapus gambar lama jika ada
            if ($user->profil) {
                Storage::disk('public')->delete($user->image);
            }

            // Menyimpan gambar baru dengan cara yang sama seperti di method store
            $imagePath = $request->file('foto_profil')->store('images', 'public');  // Menyimpan gambar di folder 'images'
            $user->foto_profil = $imagePath;
        }

        $user->save();

    Siswa::updateOrCreate(
        ['user_id' => $user->id],
        ['nama' => $request->nama]
    );

        return redirect()->route('editProfilSiswa')->with('success', 'Profile updated successfully.');
    }
}
