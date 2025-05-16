<?php

namespace App\Http\Controllers\OrangTua;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\OrangTua;

class DataOrangTua extends Controller
{
    public function index() {
        $orang_tua = OrangTua::all();
    return view('admin.event.index', compact('orang_tua'));
    } 

    public function create(){
        return view('admin.event.create');
    }
    public function store(Request $request){
        $validatedData = $request->validate([
            'nama_alumni' => 'required',
            'jenis_event' => 'required',
            'judul' => 'required',
            'gambar' => 'required',
            'keterangan' => 'required',
        ]);
        $validatedData['nama'] =strtoupper(trim($validatedData['nama']));
        OrangTua::create($validatedData);
        return redirect()->route('admin.orang_tua.index')->with('success', 'Data berhasil ditambahkan!');
    }
    public function edit($id){
        $event = OrangTua::findOrFail($id);
        return view('admin.event.edit', compact('event'));
    }
    public function update(Request $request,  $id){
        $validatedData = $request->validate([
            'nama_alumni' => 'required',
            'jenis_event' => 'required',
            'judul' => 'required',
            'gambar' => 'required',
            'keterangan' => 'required',
        ]);

        $event = OrangTua::findOrFail($id);
        $event->update($validatedData);
        return redirect()->route('admin.event.index')->with('success', 'Data berhasil diubah!');
    }
    public function destroy($id){
        OrangTua::destroy($id);
        return redirect()->route('admin.event.index')->with('success', 'Data berhasil dihapus!');
    }
}
