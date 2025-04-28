<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Guru;
use App\Models\User;

class GuruController extends Controller
{
    public function index() {
        $guru = Guru::all();
    return view('admin.guru.index', compact('guru'));
    } 

    public function create(){
        return view('admin.guru.create');
    }
    public function store(Request $request){
        $validatedData = $request->validate([
            'nama' => 'required',
            'mata_pelajaran' => 'required',
            'nip' => 'required',
            'pembina_pramuka' => 'required',
        ]);
        $validatedData['nama'] =strtoupper(trim($validatedData['nama']));
        Guru::create($validatedData);
        return redirect()->route('admin.guru.index')->with('success', 'Data berhasil ditambahkan!');
    }
    public function edit($id){
        $guru = Guru::findOrFail($id);
        return view('admin.guru.edit', compact('guru'));
    }
    public function update(Request $request,  $id){
        $validatedData = $request->validate([
            'nama' => 'required',
            'mata_pelajaran' => 'required',
            'nip' => 'required',
            'pembina_pramuka' => 'required',
        ]);

        $guru = Guru::findOrFail($id);
        $guru->update($validatedData);
        return redirect()->route('admin.guru.index')->with('success', 'Data berhasil diubah!');
    }
    public function destroy($id){
        Guru::destroy($id);
        return redirect()->route('admin.guru.index')->with('success', 'Data berhasil dihapus!');
    }
}
