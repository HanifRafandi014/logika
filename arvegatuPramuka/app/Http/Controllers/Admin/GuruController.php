<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Guru;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\GuruImport;
use Illuminate\Support\Facades\Response;

class GuruController extends Controller
{
    public function index() {
        $gurus = Guru::all();
    return view('admin.guru.index', compact('gurus'));
    } 

    public function create(){
        return view('admin.guru.create');
    }
    public function store(Request $request){
        $validatedData = $request->validate([
            'nama' => 'nullable',
            'kelas' => 'required',
            'mata_pelajaran' => 'nullable',
            'nip' => 'nullable',
            'pembina_pramuka' => 'nullable|boolean',
            'username' => 'nullable|unique:users,username',
            'password' => 'nullable|confirmed',
        ]);

        // Create user
        $user = User::create([
            'username' => $validatedData['username'],
            'password' => Hash::make($validatedData['password']),
            'role' => 'guru'
        ]);

        // Create guru
        $guru = Guru::create([
            'nama' => $validatedData['nama'],
            'kelas' => $validatedData['kelas'],
            'mata_pelajaran' => $validatedData['mata_pelajaran'],
            'nip' => $validatedData['nip'],
            'pembina_pramuka' => $validatedData['pembina_pramuka'],
            'user_id' => $user->id
        ]);
        return redirect()->route('data-guru.index')->with('success', 'Data berhasil ditambahkan!');
    }
    public function edit($id){
        $guru = Guru::findOrFail($id);
        $user = User::findOrFail($guru->user_id);
        return view('admin.guru.edit', compact('guru', 'user'));
    }
    public function update(Request $request,  $id){
        $guru = Guru::findOrFail($id);
        $user = User::findOrFail($guru->user_id);

        $validatedData = $request->validate([
            'nama' => 'nullable',
            'kelas' => 'required',
            'mata_pelajaran' => 'nullable',
            'nip' => 'nullable',
            'pembina_pramuka' => 'nullable|boolean',
            'username' => 'nullable|unique:users,username,' . $user->id,
            'password' => 'nullable|confirmed',
        ]);

        $guru->update([
            'nama' => $validatedData['nama'],
            'kelas' => $validatedData['kelas'],
            'mata_pelajaran' => $validatedData['mata_pelajaran'],
            'nip' => $validatedData['nip'],
            'pembina_pramuka' => $validatedData['pembina_pramuka'],
        ]);

        $user->username = $validatedData['username'];
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();
        return redirect()->route('data-guru.index')->with('success', 'Data berhasil diubah!');
    }
    public function destroy($id){
        $guru = Guru::findOrFail($id);
        User::destroy($guru->user_id);
        $guru->delete();

        return redirect()->route('data-guru.index')->with('success', 'Data berhasil dihapus!');
    }

    public function show($id)
    {
        return redirect()->route('data-guru.index');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        Excel::import(new GuruImport, $request->file('file'));
        return redirect()->route('data-guru.index')->with('success', 'Import berhasil!');
    }

    public function importForm()
    {
        return view('admin.guru.import'); // Pastikan file ini ada
    }

    public function downloadTemplate()
    {
        $filePath = public_path('templates/guru_template.xlsx');

        if (file_exists($filePath)) {
            return Response::download($filePath, 'guru_template.xlsx');
        } else {
            return redirect()->back()->with('error', 'Template file not found.');
        }
    }
}
