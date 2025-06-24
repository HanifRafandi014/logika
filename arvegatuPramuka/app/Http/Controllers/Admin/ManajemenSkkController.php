<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ManajemenSkk;

class ManajemenSkkController extends Controller
{
    public function index() {
        $skks = ManajemenSkk::all();
    return view('admin.manajemen_skk.index', compact('skks'));
    }

    public function create(){
        $tingkatans = ['purwa', 'madya', 'utama'];
        return view('admin.manajemen_skk.create', compact('tingkatans'));
    }
    public function store(Request $request){
        $validatedData = $request->validate([
            'jenis_skk' => 'required|string',
            'keterangan_skk' => 'required|string',
            'tingkatan' => 'required|in:purwa,madya,utama',
        ]);

        // Create guru
        $skk = ManajemenSkk::create([
            'jenis_skk' => $validatedData['jenis_skk'],
            'keterangan_skk' => $validatedData['keterangan_skk'],
            'tingkatan' => $validatedData['tingkatan'],
        ]);
        return redirect()->route('manajemen_skk.index')->with('success', 'Data berhasil ditambahkan!');
    }
    public function edit($id){
        $skk = ManajemenSkk::findOrFail($id);
        $tingkatans = ['purwa', 'madya', 'utama'];
        return view('admin.manajemen_skk.edit', compact('skk', 'tingkatans'));
    }
    public function update(Request $request,  $id){
        $skk = ManajemenSkk::findOrFail($id);

        $validatedData = $request->validate([
            'jenis_skk' => 'required|string',
            'keterangan_skk' => 'required|string',
            'tingkatan' => 'required|in:purwa,madya,utama',
        ]);

        $skk->update([
            'jenis_skk' => $validatedData['jenis_skk'],
            'keterangan_skk' => $validatedData['keterangan_skk'],
            'tingkatan' => $validatedData['tingkatan'],
        ]);

        return redirect()->route('manajemen_skk.index')->with('success', 'Data berhasil diubah!');
    }
    public function destroy($id){
        $skk = ManajemenSkk::findOrFail($id);
        $skk->delete();

        return redirect()->route('manajemen_skk.index')->with('success', 'Data berhasil dihapus!');
    }
}
