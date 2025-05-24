<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Siswa;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\SiswaImport;

class SiswaController extends Controller
{
    public function index() {
        $siswas = Siswa::all();
    return view('admin.siswa.index', compact('siswas'));
    } 

    public function create(){
        return view('admin.siswa.create');
    }
    public function store(Request $request){
        $validatedData = $request->validate([
            'nama' => 'required',
            'kelas' => 'required',
            'nisn' => 'required',
            'angkatan' => 'required',
            'kelas_pramuka' => 'required',
            'username' => 'nullable|unique:users,username',
            'password' => 'nullable|confirmed',
        ]);
        // Create user
        $user = User::create([
            'username' => $validatedData['username'],
            'password' => Hash::make($validatedData['password']),
            'role' => 'siswa'
        ]);

        // Create guru
        $siswa = Siswa::create([
            'nama' => $validatedData['nama'],
            'kelas' => $validatedData['kelas'],
            'nisn' => $validatedData['nisn'],
            'angkatan' => $validatedData['angkatan'],
            'kelas_pramuka' => $validatedData['kelas_pramuka'],
            'user_id' => $user->id
        ]);
        return redirect()->route('data-siswa.index')->with('success', 'Data berhasil ditambahkan!');
    }
    public function edit($id){
        $siswa = Siswa::findOrFail($id);
        $user = User::findOrFail($siswa->user_id);
        return view('admin.siswa.edit', compact('siswa', 'user'));
    }
    public function update(Request $request,  $id){
        $siswa = Siswa::findOrFail($id);
        $user = User::findOrFail($siswa->user_id);

        $validatedData = $request->validate([
            'nama' => 'required',
            'kelas' => 'required',
            'nisn' => 'required',
            'angkatan' => 'required',
            'kelas_pramuka' => 'required',
            'username' => 'nullable|unique:users,username,' . $user->id,
            'password' => 'nullable|confirmed',
        ]);

        $siswa->update([
            'nama' => $validatedData['nama'],
            'kelas' => $validatedData['kelas'],
            'nisn' => $validatedData['nisn'],
            'angkatan' => $validatedData['angkatan'],
            'kelas_pramuka' => $validatedData['kelas_pramuka'],
        ]);

        $user->username = $validatedData['username'];
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();
        return redirect()->route('data-siswa.index')->with('success', 'Data berhasil diubah!');
    }
    public function destroy($id){
        $siswa = Siswa::findOrFail($id);
        User::destroy($siswa->user_id);
        $siswa->delete();
        return redirect()->route('data-siswa.index')->with('success', 'Data berhasil dihapus!');
    }

    public function show($id)
    {
        return redirect()->route('data-siswa.index');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        Excel::import(new SiswaImport, $request->file('file'));
        return redirect()->route('data-siswa.index')->with('success', 'Import berhasil!');
    }

    public function importForm()
    {
        return view('admin.siswa.import'); // Pastikan file ini ada
    }
}
