<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Pembina;
use App\Models\User;

class PembinaController extends Controller
{
    public function index() {
        $pembina = Pembina::all();
    return view('admin.pembina.index', compact('pembina'));
    } 

    public function create(){
        return view('admin.pembina.create');
    }
    public function store(Request $request){
        $validatedData = $request->validate([
            'nama' => 'required',
            'mata_pelajaran' => 'required',
            'nip' => 'required',
            'pembina_pramuka' => 'required',
        ]);
        $validatedData['nama'] =strtoupper(trim($validatedData['nama']));
        Pembina::create($validatedData);
        return redirect()->route('admin.pembina.index')->with('success', 'Data berhasil ditambahkan!');
    }
    public function edit($id){
        $pembina = Pembina::findOrFail($id);
        return view('admin.pembina.edit', compact('pembina'));
    }
    public function update(Request $request,  $id){
        $validatedData = $request->validate([
            'nama' => 'required',
            'mata_pelajaran' => 'required',
            'nip' => 'required',
            'pembina_pramuka' => 'required',
        ]);

        $pembina = Pembina::findOrFail($id);
        $pembina->update($validatedData);
        return redirect()->route('admin.pembina.index')->with('success', 'Data berhasil diubah!');
    }
    public function destroy($id){
        Pembina::destroy($id);
        return redirect()->route('admin.pembina.index')->with('success', 'Data berhasil dihapus!');
    }
}
