<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Alumni;
use App\Models\User;

class AlumniController extends Controller
{
    public function index() {
        $alumni = Alumni::all();
    return view('admin.alumni.index', compact('alumni'));
    } 

    public function create(){
        return view('admin.alumni.create');
    }
    public function store(Request $request){
        $validatedData = $request->validate([
            'nama' => 'required',
            'tahun_lulus' => 'required',
            'pekerjaan' => 'required',
            'no_hp' => 'required',
        ]);
        $validatedData['nama'] =strtoupper(trim($validatedData['nama']));
        Alumni::create($validatedData);
        return redirect()->route('admin.alumni.index')->with('success', 'Data berhasil ditambahkan!');
    }
    public function edit($id){
        $alumni = Alumni::findOrFail($id);
        return view('admin.alumni.edit', compact('alumni'));
    }
    public function update(Request $request,  $id){
        $validatedData = $request->validate([
            'nama' => 'required',
            'tahun_lulus' => 'required',
            'pekerjaan' => 'required',
            'no_hp' => 'required',
        ]);

        $alumni = Alumni::findOrFail($id);
        $alumni->update($validatedData);
        return redirect()->route('admin.alumni.index')->with('success', 'Data berhasil diubah!');
    }
    public function destroy($id){
        Alumni::destroy($id);
        return redirect()->route('admin.alumni.index')->with('success', 'Data berhasil dihapus!');
    }
}
