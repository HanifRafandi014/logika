<?php

namespace App\Http\Controllers\Pembina;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Pembina;
use App\Models\User;

class ProfilPembinaController extends Controller
{
    public function index()
    {
        $user = Auth::user(); // ambil user yang login
        $pembina = Pembina::where('user_id', $user->id)->first();

        if (!$pembina) {
            return redirect()->back()->with('error', 'Data pembina tidak ditemukan.');
        }

        return view('pembina.profil_pembina', compact('pembina', 'user'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'kategori' => 'required',
        ]);

        $user = Auth::user();
        $pembina = Pembina::where('user_id', $user->id)->first();

        if (!$pembina) {
            return redirect()->back()->with('error', 'Data pembina tidak ditemukan.');
        }

        // Simpan perubahan
        $pembina->update($request->all());

        return redirect()->back()->with('success', 'Profil berhasil diperbarui.');
    }
}
