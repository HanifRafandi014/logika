<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\OrangTua;
use App\Models\User;

class OrangTuaController extends Controller
{
    public function index() {
        $orang_tua = OrangTua::all();
    return view('admin.orang_tua.index', compact('guru'));
    } 

    public function create(){
        return view('admin.orang_tua.create');
    }
    public function store(Request $request){
        $validatedData = $request->validate([
            'nama' => 'required',
            'mata_pelajaran' => 'required',
            'nip' => 'required',
            'pembina_pramuka' => 'required',
        ]);
        $validatedData['nama'] =strtoupper(trim($validatedData['nama']));
        OrangTua::create($validatedData);
        return redirect()->route('admin.orang-tua.index')->with('success', 'Data berhasil ditambahkan!');
    }
    public function edit($id){
        $orangTua = OrangTua::findOrFail($id);
        return view('admin.orang_tua.edit', compact('orangTua'));
    }
    public function update(Request $request,  $id){
        $validatedData = $request->validate([
            'nama' => 'required',
            'mata_pelajaran' => 'required',
            'nip' => 'required',
            'pembina_pramuka' => 'required',
        ]);

        $orang_tua = OrangTua::findOrFail($id);
        $orang_tua->update($validatedData);
        return redirect()->route('admin.orang-tua.index')->with('success', 'Data berhasil diubah!');
    }
    public function destroy($id){
        OrangTua::destroy($id);
        return redirect()->route('admin.orang-tua.index')->with('success', 'Data berhasil dihapus!');
    }
}
