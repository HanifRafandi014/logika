<?php

namespace App\Http\Controllers\OrangTua;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Hash; 
use App\Models\User;
use App\Models\OrangTua;
use Illuminate\Support\Facades\Storage;

class EditProfilOrangTuaController extends Controller
{
    public function editProfilOrangTua()
    {
        $user = Auth::user();
        $orangTua = $user->orangTua;
        if(!$orangTua){
            $orangTua = new OrangTua();
        }

        return view('orang_tua.editProfil-orangTua', compact('user', 'orangTua'));
    }

    public function updateProfilOrangTua(Request $request)
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

    OrangTua::updateOrCreate(
        ['user_id' => $user->id],
        ['nama' => $request->nama]
    );

        return redirect()->route('editProfilOrangTua')->with('success', 'Profile updated successfully.');
    }
}
