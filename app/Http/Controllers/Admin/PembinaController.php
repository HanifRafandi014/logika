<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Pembina;
use App\Models\Guru;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PembinaImport;

class PembinaController extends Controller
{
    public function index() {
        $pembinas = Pembina::with('guru')->get();
    return view('admin.pembina.index', compact('pembinas'));
    } 

    public function create(){
        $guru = Guru::all();
        return view('admin.pembina.create', compact('guru'));
    }
    public function store(Request $request){
        $validatedData = $request->validate([
            'nama' => 'required',
            'nip' => 'required',
        ]);
        $validatedData['nama'] =strtoupper(trim($validatedData['nama']));
        Pembina::create($validatedData);
        return redirect()->route('data-pembina.index')->with('success', 'Data berhasil ditambahkan!');
    }
    public function edit($id){
        $pembina = Pembina::findOrFail($id);
        return view('admin.pembina.edit', compact('pembina'));
    }
    public function update(Request $request,  $id){
        $validatedData = $request->validate([
            'nama' => 'required',
            'nip' => 'required',
        ]);

        $pembina = Pembina::findOrFail($id);
        $pembina->update($validatedData);
        return redirect()->route('data-pembina.index')->with('success', 'Data berhasil diubah!');
    }
    public function destroy($id){
        Pembina::destroy($id);
        return redirect()->route('data-pembina.index')->with('success', 'Data berhasil dihapus!');
    }
}
