<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Pembina;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PembinaImport;
use App\Exports\PembinaExport;

class PembinaController extends Controller
{
    public function index() {
        $pembinas = Pembina::all();
    return view('admin.pembina.index', compact('pembinas'));
    } 

    public function create(){
        $pembinaOptions = [
            1 => 'Pembina PA',
            0 => 'Pembina PI',
        ];
        return view('admin.pembina.create', compact('pembinaOptions'));
    }
    public function store(Request $request){
        $validatedData = $request->validate([
            'nama' => 'required',
            'kelas' => 'required',
            'nip' => 'required',
            'kategori' => 'required',
            'status' => 'required',
            'username' => 'nullable|unique:users,username',
            'password' => 'nullable|confirmed',
        ]);

        // Create user
        $user = User::create([
            'username' => $validatedData['username'],
            'password' => Hash::make($validatedData['password']),
            'role' => 'pembina'
        ]);

        // Create pembina
        $pembina = Pembina::create([
            'nama' => $validatedData['nama'],
            'kelas' => $validatedData['kelas'],
            'nip' => $validatedData['nip'],
            'kategori' => $validatedData['kategori'],
            'status' => $validatedData['status'],
            'user_id' => $user->id
        ]);
        return redirect()->route('data-pembina.index')->with('success', 'Data berhasil ditambahkan!');
    }
    public function edit($id){
        $pembina = Pembina::findOrFail($id);
        $user = User::findOrFail($pembina->user_id);
        $pembinaOptions = [
            1 => 'Pembina PA',
            0 => 'Pembina PI',
        ];
        return view('admin.pembina.edit', compact('pembina', 'user', 'pembinaOptions'));
    }
    public function update(Request $request,  $id){
        $pembina = Pembina::findOrFail($id);
        $user = User::findOrFail($pembina->user_id);

        $validatedData = $request->validate([
            'nama' => 'required',
            'kelas' => 'required',
            'nip' => 'required',
            'kategori' => 'required',
            'status' => 'required',
            'username' => 'nullable|unique:users,username,' . $user->id,
            'password' => 'nullable|confirmed',
        ]);

        $pembina->update([
            'nama' => $validatedData['nama'],
            'kelas' => $validatedData['kelas'],
            'nip' => $validatedData['nip'],
            'kategori' => $validatedData['kategori'],
            'status' => $validatedData['status'],
        ]);

        $user->username = $validatedData['username'];
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();
        return redirect()->route('data-pembina.index')->with('success', 'Data berhasil diubah!');
    }
    public function destroy($id){
        $pembina = Pembina::findOrFail($id);
        User::destroy($pembina->user_id);
        $pembina->delete();

        return redirect()->route('data-pembina.index')->with('success', 'Data berhasil dihapus!');
    }

    public function show($id)
    {
        return redirect()->route('data-pembina.index');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        Excel::import(new pembinaImport, $request->file('file'));
        return redirect()->route('data-pembina.index')->with('success', 'Import berhasil!');
    }

    public function importForm()
    {
        return view('admin.pembina.import'); // Pastikan file ini ada
    }

    public function pembinaExport(Request $request)
    {
        $kategoriFilter = $request->query('kategori');
        $statusFilter = $request->query('status');

        $export = new PembinaExport();
        return $export->export($kategoriFilter, $statusFilter);
    }
}
