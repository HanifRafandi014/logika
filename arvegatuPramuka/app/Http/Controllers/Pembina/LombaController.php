<?php

namespace App\Http\Controllers\Pembina;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lomba;
use App\Models\VariabelClustering;

class LombaController extends Controller
{
    public function index()
    {
        $lombas = Lomba::with('variabel')->get();
        return view('pembina.lomba.index', compact('lombas'));
    }

    public function create()
    {
        // Tidak perlu with() karena variabel_akademiks & variabel_non_akademiks sudah berupa JSON array
        $variabels = VariabelClustering::all();
        return view('pembina.lomba.create', compact('variabels'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'variabel_clustering_id' => 'required|exists:variabel_clusterings,id',
            'jumlah_siswa'           => 'required|integer|min:1',
            'status'                 => 'required|boolean',
        ]);

        Lomba::create($validated);

        return redirect()->route('lomba.index')->with('success', 'Lomba berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $lomba = Lomba::findOrFail($id);
        $variabels = VariabelClustering::all();

        return view('pembina.lomba.edit', compact('lomba', 'variabels'));
    }

    public function update(Request $request, $id)
    {
        $lomba = Lomba::findOrFail($id);

        // Jika variabel_clustering_id tidak dikirim (karena disabled), gunakan nilai lama
        if (! $request->has('variabel_clustering_id')) {
            $request->merge(['variabel_clustering_id' => $lomba->variabel_clustering_id]);
        }

        $validated = $request->validate([
            'variabel_clustering_id' => 'required|exists:variabel_clusterings,id',
            'jumlah_siswa'           => 'required|integer|min:1',
            'status'                 => 'required|boolean',
        ]);

        $lomba->update($validated);

        return redirect()->route('lomba.index')->with('success', 'Lomba berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $lomba = Lomba::findOrFail($id);
        $lomba->delete();

        return redirect()->route('lomba.index')->with('success', 'Lomba berhasil dihapus!');
    }
}
