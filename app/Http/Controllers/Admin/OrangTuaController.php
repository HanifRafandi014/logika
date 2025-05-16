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
        return view('admin.orang_tua.create', compact('siswas'));
    }
    public function store(Request $request){
        $validatedData = $request->validate([
            'nama' => 'required',
            'no_hp' => 'required',
            'alamat' => 'required',
            'siswa_id' => 'nullable',
        ]);
        
        $orangTua = OrangTua::create([
            'nama' => $validatedData['nama'],
            'no_hp' => $validatedData['no_hp'],
            'alamat' => $validatedData['alamat'],
            'siswa_id' => $validatedData['siswa_id']
        ]);

        return redirect()->route('data-orang-tua.index')->with('success', 'Data berhasil ditambahkan!');
    }
    public function edit($id){
        $siswas = Siswa::all();
        $orangTua = OrangTua::findOrFail($id);
        return view('admin.orang_tua.edit', compact('orangTua', 'siswas'));
    }
    public function update(Request $request,  $id){
        $orangTua = OrangTua::findOrFail($id);

        $validatedData = $request->validate([
            'nama' => 'required',
            'no_hp' => 'required',
            'alamat' => 'required',
            'siswa_id' => 'nullable',
        ]);

        $orangTua->update([
            'nama' => $validatedData['nama'],
            'no_hp' => $validatedData['no_hp'],
            'alamat' => $validatedData['alamat'],
            'siswa_id' => $validatedData['siswa_id']
        ]);

        return redirect()->route('data-orang-tua.index')->with('success', 'Data berhasil diubah!');
    }
    public function destroy($id){
        OrangTua::destroy($id);
        return redirect()->route('data-orang-tua.index')->with('success', 'Data berhasil dihapus!');
    }
}
