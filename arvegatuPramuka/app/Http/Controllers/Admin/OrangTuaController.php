<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\OrangTua;
use App\Models\Siswa;
use App\Models\User;

class OrangTuaController extends Controller
{
    public function index() {
        $orangTuas = OrangTua::with('siswa')->get();
    return view('admin.orang_tua.index', compact('orangTuas'));
    } 

    public function create(){
        $siswas = Siswa::all();
        $statuss = ['Anggota', 'Pengurus Paguyuban Kelas', 'Pengurus Paguyuban Besar'];
        return view('admin.orang_tua.create', compact('siswas', 'statuss'));
    }
    public function store(Request $request){
        $validatedData = $request->validate([
            'nama' => 'required|string|max:255',
            'no_hp' => 'required|string|max:20',
            'alamat' => 'required|string|max:255',
            'status' => 'required|in:Anggota,Pengurus Paguyuban Kelas,Pengurus Paguyuban Besar',
            'username' => 'nullable|unique:users,username',
            'password' => 'nullable|confirmed',
            'siswa_id' => 'nullable|exists:siswas,id',
        ]);
        // Create user
        $user = User::create([
            'username' => $validatedData['username'],
            'password' => Hash::make($validatedData['password']),
            'role' => 'orang_tua'
        ]);
        
        $orangTua = OrangTua::create([
            'nama' => $validatedData['nama'],
            'no_hp' => $validatedData['no_hp'],
            'alamat' => $validatedData['alamat'],
            'status' => $validatedData['status'],
            'siswa_id' => $validatedData['siswa_id'],
            'user_id' => $user->id
        ]);

        return redirect()->route('data-orang-tua.index')->with('success', 'Data berhasil ditambahkan!');
    }
    public function edit($id){
        $siswas = Siswa::all();
        $orangTua = OrangTua::findOrFail($id);
        $user = User::findOrFail($orangTua->user_id);
        $statuss = ['Anggota', 'Pengurus Paguyuban Kelas', 'Pengurus Paguyuban Besar'];
        return view('admin.orang_tua.edit', compact('orangTua', 'siswas', 'statuss', 'user'));
    }
    public function update(Request $request,  $id){
        $orangTua = OrangTua::findOrFail($id);
        $user = User::findOrFail($orangTua->user_id);

        $validatedData = $request->validate([
            'nama' => 'required|string|max:255',
            'no_hp' => 'required|string|max:20',
            'alamat' => 'required|string|max:255',
            'status' => 'required|in:Anggota,Pengurus Paguyuban Kelas,Pengurus Paguyuban Besar',
            'username' => 'nullable|unique:users,username,' . $user->id,
            'password' => 'nullable|confirmed',
            'siswa_id' => 'nullable|exists:siswas,id',
        ]);

        $orangTua->update([
            'nama' => $validatedData['nama'],
            'no_hp' => $validatedData['no_hp'],
            'alamat' => $validatedData['alamat'],
            'status' => $validatedData['status'],
            'siswa_id' => $validatedData['siswa_id']
        ]);
        $user->username = $validatedData['username'];
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('data-orang-tua.index')->with('success', 'Data berhasil diubah!');
    }

    public function show($id)
    {
        $orangTua = OrangTua::with(['user', 'siswa'])->findOrFail($id);
        return view('admin.orang_tua.show', compact('orangTua'));
    }

    public function destroy($id){
        $orangTua = OrangTua::findOrFail($id);
        User::destroy($orangTua->user_id);
        $orangTua->delete();
        return redirect()->route('data-orang-tua.index')->with('success', 'Data berhasil dihapus!');
    }
}
