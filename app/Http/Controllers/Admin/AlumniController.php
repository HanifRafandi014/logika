<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Alumni;
use App\Models\Siswa;
use App\Models\User;

class AlumniController extends Controller
{
    public function index() {
        $alumnis = Alumni::with('siswa')->get();
    return view('admin.alumni.index', compact('alumnis'));
    } 

    public function create(){
        $siswas = Siswa::all();
        return view('admin.alumni.create', compact('siswas'));
    }
    public function store(Request $request){
        $validatedData = $request->validate([
            'tahun_lulus' => 'required',
            'pekerjaan' => 'required',
            'no_hp' => 'required',
            'siswa_id' => 'nullable',
        ]);

        $alumni = Alumni::create([
            'tahun_lulus' => $validatedData['tahun_lulus'],
            'pekerjaan' => $validatedData['pekerjaan'],
            'no_hp' => $validatedData['no_hp'],
            'siswa_id' => $validatedData['siswa_id']
        ]);
        return redirect()->route('data-alumni.index')->with('success', 'Data berhasil ditambahkan!');
    }
    public function edit($id){
        $siswas = Siswa::all();
        $alumni = Alumni::findOrFail($id);
        return view('admin.alumni.edit', compact('alumni', 'siswas'));
    }
    public function update(Request $request,  $id){
        $alumni = Alumni::findOrFail($id);

        $validatedData = $request->validate([
            'tahun_lulus' => 'required',
            'pekerjaan' => 'required',
            'no_hp' => 'required',
            'siswa_id' => 'nullable',
        ]);

        $alumni->update([
            'tahun_lulus' => $validatedData['tahun_lulus'],
            'pekerjaan' => $validatedData['pekerjaan'],
            'no_hp' => $validatedData['no_hp'],
            'siswa_id' => $validatedData['siswa_id']
        ]);

        return redirect()->route('data-alumni.index')->with('success', 'Data berhasil diubah!');
    }
    public function destroy($id){
        Alumni::destroy($id);
        return redirect()->route('data-alumni.index')->with('success', 'Data berhasil dihapus!');
    }
}
