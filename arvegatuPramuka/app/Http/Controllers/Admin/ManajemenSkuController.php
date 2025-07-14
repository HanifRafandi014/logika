<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ManajemenSku;

class ManajemenSkuController extends Controller
{
    public function index() {
        $skus = ManajemenSku::all();
    return view('admin.manajemen_sku.index', compact('skus'));
    }

    public function create(){
        $tingkatans = ['Ramu', 'Rakit', 'Terap'];
        return view('admin.manajemen_sku.create', compact('tingkatans'));
    }
    public function store(Request $request){
        $validatedData = $request->validate([
            'keterangan_sku' => 'required|string',
            'item_pencapaian_sku' => 'required|string',
            'tingkatan' => 'required|in:Ramu,Rakit,Terap',
        ]);

        // Create guru
        $sku = ManajemenSku::create([
            'keterangan_sku' => $validatedData['keterangan_sku'],
            'item_pencapaian_sku' => $validatedData['item_pencapaian_sku'],
            'tingkatan' => $validatedData['tingkatan'],
        ]);
        return redirect()->route('manajemen_sku.index')->with('success', 'Data berhasil ditambahkan!');
    }
    public function edit($id){
        $sku = ManajemenSku::findOrFail($id);
        $tingkatans = ['Ramu', 'Rakit', 'Terap'];
        return view('admin.manajemen_sku.edit', compact('sku', 'tingkatans'));
    }
    public function update(Request $request,  $id){
        $sku = ManajemenSku::findOrFail($id);

        $validatedData = $request->validate([
            'keterangan_sku' => 'required|string',
            'item_pencapaian_sku' => 'required|string',
            'tingkatan' => 'required|in:Ramu,Rakit,Terap',
        ]);

        $sku->update([
            'keterangan_sku' => $validatedData['keterangan_sku'],
            'item_pencapaian_sku' => $validatedData['item_pencapaian_sku'],
            'tingkatan' => $validatedData['tingkatan'],
        ]);

        return redirect()->route('manajemen_sku.index')->with('success', 'Data berhasil diubah!');
    }
    public function destroy($id){
        $sku = ManajemenSku::findOrFail($id);
        $sku->delete();

        return redirect()->route('manajemen_sku.index')->with('success', 'Data berhasil dihapus!');
    }
}
