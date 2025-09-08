<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Variabel;
use App\Models\VariabelClustering;

class VariabelController extends Controller
{
    public function index()
    {
        $variabels = VariabelClustering::all();
        return view('admin.variabel.index', compact('variabels'));
    }

    public function create()
    {
        return view('admin.variabel.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'jenis_lomba' => 'required|string|max:255',
        ]);

        VariabelClustering::create([
            'jenis_lomba' => $request->jenis_lomba,
            'variabel_akademiks' => json_encode($request->variabel_akademik ?? []),
            'variabel_non_akademiks' => json_encode($request->variabel_non_akademik ?? []),
        ]);

        return redirect()->route('data-variabel.index')->with('success', 'Data berhasil ditambahkan');
    }

    public function edit($id)
    {
        $variabel = VariabelClustering::findOrFail($id);
        return view('admin.variabel.edit', compact('variabel'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'jenis_lomba' => 'required|string|max:255',
        ]);

        $variabel = VariabelClustering::findOrFail($id);
        $variabel->update([
            'jenis_lomba' => $request->jenis_lomba,
            'variabel_akademiks' => json_encode($request->variabel_akademik ?? []),
            'variabel_non_akademiks' => json_encode($request->variabel_non_akademik ?? []),
        ]);

        return redirect()->route('data-variabel.index')->with('success', 'Data berhasil diperbarui');
    }

    public function destroy($id)
    {
        $variabel = VariabelClustering::findOrFail($id);
        $variabel->delete();

        return redirect()->route('data-variabel.index')->with('success', 'Data berhasil dihapus');
    }
}
