<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Siswa;
use App\Models\User;

class SiswaController extends Controller
{
    public function index() {
        $siswa = Siswa::all();
    return view('admin.siswa.index', compact('siswa'));
    } 

    public function create(){
        return view('admin.siswa.create');
    }
    public function store(Request $request){
        $validatedData = $request->validate([
            'nama' => 'required',
            'mata_pelajaran' => 'required',
            'nip' => 'required',
            'pembina_pramuka' => 'required',
        ]);
        $validatedData['nama'] =strtoupper(trim($validatedData['nama']));
        Siswa::create($validatedData);
        return redirect()->route('admin.siswa.index')->with('success', 'Data berhasil ditambahkan!');
    }
    public function edit($id){
        $siswa = Siswa::findOrFail($id);
        return view('admin.siswa.edit', compact('siswa'));
    }
    public function update(Request $request,  $id){
        $validatedData = $request->validate([
            'nama' => 'required',
            'mata_pelajaran' => 'required',
            'nip' => 'required',
            'pembina_pramuka' => 'required',
        ]);

        $siswa = Siswa::findOrFail($id);
        $siswa->update($validatedData);
        return redirect()->route('admin.siswa.index')->with('success', 'Data berhasil diubah!');
    }
    public function destroy($id){
        Siswa::destroy($id);
        return redirect()->route('admin.siswa.index')->with('success', 'Data berhasil dihapus!');
    }
}
