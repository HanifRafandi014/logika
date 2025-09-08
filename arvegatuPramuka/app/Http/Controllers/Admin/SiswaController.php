<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Siswa;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\SiswaImport;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\Rule;

class SiswaController extends Controller
{
    public function index() {
        $siswas = Siswa::all();
    return view('admin.siswa.index', compact('siswas'));
    } 

    public function create(){
        $jenisKelaminOptions = [
            1 => 'Laki-laki',
            0 => 'Perempuan',
        ];
        return view('admin.siswa.create', compact('jenisKelaminOptions'));
    }
    public function store(Request $request){
        $validatedData = $request->validate([
            'nama' => 'required',
            'kelas' => 'required',
            'nisn' => 'required',
            'angkatan' => 'required',
            'jenis_kelamin' => 'required',
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
            'jenis_kelamin' => $validatedData['jenis_kelamin'],
            'user_id' => $user->id
        ]);
        return redirect()->route('data-siswa.index')->with('success', 'Data berhasil ditambahkan!');
    }
    public function edit($id){
        $siswa = Siswa::findOrFail($id);
        $user = User::findOrFail($siswa->user_id);
        $jenisKelaminOptions = [
            1 => 'Laki-laki',
            0 => 'Perempuan',
        ];
        return view('admin.siswa.edit', compact('siswa', 'user', 'jenisKelaminOptions'));
    }
    public function update(Request $request,  $id){
        $siswa = Siswa::findOrFail($id);
        $user = User::findOrFail($siswa->user_id);

        $validatedData = $request->validate([
            'nama' => 'required',
            'kelas' => 'required',
            'nisn' => 'required',
            'angkatan' => 'required',
            'jenis_kelamin' => 'required',
            'username' => 'nullable|unique:users,username,' . $user->id,
            'password' => 'nullable|confirmed',
        ]);

        $siswa->update([
            'nama' => $validatedData['nama'],
            'kelas' => $validatedData['kelas'],
            'nisn' => $validatedData['nisn'],
            'angkatan' => $validatedData['angkatan'],
            'jenis_kelamin' => $validatedData['jenis_kelamin'],
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
        $siswa = Siswa::with(['user', 'nilai_akademik', 'nilai_non_akademik'])->findOrFail($id);
        return view('admin.siswa.show', compact('siswa'));
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

    public function downloadTemplate()
    {
        $filePath = public_path('templates/siswa_template.xlsx');

        if (file_exists($filePath)) {
            return Response::download($filePath, 'siswa_template.xlsx');
        } else {
            return redirect()->back()->with('error', 'Template file not found.');
        }
    }
}
