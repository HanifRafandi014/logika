<?php

namespace App\Http\Controllers\Pembina;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lomba;
use App\Models\NilaiAkademik;
use App\Models\NilaiNonAkademik;

class LombaController extends Controller
{
    public function index()
    {
        // No need for 'with' on relationships that are now JSON columns
        $lombas = Lomba::all();
        return view('pembina.lomba.index', compact('lombas'));
    }

    public function create()
    {
        $nilaiAkademiks = NilaiAkademik::all();
        $nilaiNonAkademiks = NilaiNonAkademik::all();
        return view('pembina.lomba.create', compact('nilaiAkademiks', 'nilaiNonAkademiks'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'jenis_lomba'           => 'required|string|max:255',
            'jumlah_siswa'          => 'required|integer',
            'status'                => 'boolean',
            'nilai_akademiks'    => 'nullable|array',
            'nilai_akademiks.*'  => 'exists:nilai_akademiks,id', // Ensure each ID exists
            'nilai_non_akademiks' => 'nullable|array',
            'nilai_non_akademiks.*' => 'exists:nilai_non_akademiks,id', // Ensure each ID exists
        ]);

        // Convert checkbox status to boolean
        $validatedData['status'] = $request->has('status');
        Lomba::create($validatedData);

        return redirect()->route('lomba.index')->with('success', 'Data lomba berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $lomba = Lomba::findOrFail($id);
        $nilaiAkademiks = NilaiAkademik::all();
        $nilaiNonAkademiks = NilaiNonAkademik::all();
        return view('pembina.lomba.edit', compact('lomba', 'nilaiAkademiks', 'nilaiNonAkademiks'));
    }

    public function update(Request $request, $id)
    {
        $lomba = Lomba::findOrFail($id);

        $validatedData = $request->validate([
            'jenis_lomba'           => 'required|string|max:255',
            'jumlah_siswa'          => 'required|integer',
            'status'                => 'boolean',
            'nilai_akademiks'    => 'nullable|array',
            'nilai_akademiks.*'  => 'exists:nilai_akademiks,id',
            'nilai_non_akademiks' => 'nullable|array',
            'nilai_non_akademiks.*' => 'exists:nilai_non_akademiks,id',
        ]);

        $validatedData['status'] = $request->has('status');

        $lomba->update($validatedData);

        return redirect()->route('lomba.index')->with('success', 'Data lomba berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $lomba = Lomba::findOrFail($id);
        $lomba->delete();

        return redirect()->route('lomba.index')->with('success', 'Data lomba berhasil dihapus!');
    }
}

