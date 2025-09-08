<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BesaranBiaya;

class BesaranBiayaController extends Controller
{
    public function index()
    {
        $biayas = BesaranBiaya::all();
        return view('admin.besaran_biaya.index', compact('biayas'));
    }

    public function create()
    {
        return view('admin.besaran_biaya.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nominal_pagu_kelas' => 'required|integer|min:0', 
            'nominal_pagu_besar' => 'required|integer|min:0', 
        ]);

        $totalBiaya = $validatedData['nominal_pagu_kelas'] + $validatedData['nominal_pagu_besar'];

        $biaya = BesaranBiaya::create([
            'nominal_pagu_kelas' => $validatedData['nominal_pagu_kelas'],
            'nominal_pagu_besar' => $validatedData['nominal_pagu_besar'],
            'total_biaya' => $totalBiaya, 
        ]);

        return redirect()->route('data-besaran-biaya.index')->with('success', 'Data besaran biaya berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $biaya = BesaranBiaya::findOrFail($id);
        return view('admin.besaran_biaya.edit', compact('biaya'));
    }

    public function update(Request $request, $id)
    {
        $biaya = BesaranBiaya::findOrFail($id);

        $validatedData = $request->validate([
            'nominal_pagu_kelas' => 'required|integer|min:0', 
            'nominal_pagu_besar' => 'required|integer|min:0', 
        ]);

        $totalBiaya = $validatedData['nominal_pagu_kelas'] + $validatedData['nominal_pagu_besar'];
        $biaya->update([
            'nominal_pagu_kelas' => $validatedData['nominal_pagu_kelas'],
            'nominal_pagu_besar' => $validatedData['nominal_pagu_besar'],
            'total_biaya' => $totalBiaya, // Perbarui dengan hasil perhitungan
        ]);

        return redirect()->route('data-besaran-biaya.index')->with('success', 'Data besaran biaya berhasil diubah!');
    }

    public function destroy($id)
    {
        $biaya = BesaranBiaya::findOrFail($id);
        $biaya->delete();
        return redirect()->route('data-besaran-biaya.index')->with('success', 'Data besaran biaya berhasil dihapus!');
    }
}
