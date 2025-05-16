<?php

namespace App\Http\Controllers\Alumni;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Hash; 
use App\Models\User;
use App\Models\Alumni;
use Illuminate\Support\Facades\Storage;

class EditProfilAlumniController extends Controller
{
    public function editProfilAlumni()
    {
        $user = Auth::user();
        $alumni = $user->alumni;
        if(!$alumni){
            $alumni = new Alumni();
        }

        return view('alumni.editProfil-alumni', compact('user', 'alumni'));
    }

    public function updateProfilAlumni(Request $request)
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

    Alumni::updateOrCreate(
        ['user_id' => $user->id],
        ['nama' => $request->nama]
    );

        return redirect()->route('editProfilAlumni')->with('success', 'Profile updated successfully.');
    }
}
