<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\OrangTua;
use App\Models\Siswa;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel; // Import Excel Facade
use App\Imports\OrangTuaImport; // Import your OrangTuaImport class
// use App\Exports\OrangTuaTemplateExport; 

class OrangTuaController extends Controller
{
    public function index()
    {
        // Eager load siswa and user relationships for efficiency
        $orangTuas = OrangTua::with('siswa', 'user')->get();
        return view('admin.orang_tua.index', compact('orangTuas'));
    }

    public function create()
    {
        $siswas = Siswa::all();
        $statuss = ['Anggota', 'Pengurus Paguyuban Kelas', 'Pengurus Paguyuban Besar'];
        return view('admin.orang_tua.create', compact('siswas', 'statuss'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama' => 'required|string|max:255',
            'no_hp' => 'required|string|max:20',
            'alamat' => 'required|string|max:255',
            'status' => 'required|in:Anggota,Pengurus Paguyuban Kelas,Pengurus Paguyuban Besar',
            'username' => 'required|unique:users,username', // Username is now required for creation
            'password' => 'required|confirmed|min:6', // Password is required for creation
            'siswa_id' => 'nullable|exists:siswas,id',
        ]);

        // Create user
        $user = User::create([
            'username' => $validatedData['username'],
            'password' => Hash::make($validatedData['password']),
            'role' => 'orang_tua'
        ]);

        $orangTua = OrangTua::create([
            'nama' => $validatedData['nama'],
            'no_hp' => $validatedData['no_hp'],
            'alamat' => $validatedData['alamat'],
            'status' => $validatedData['status'],
            'siswa_id' => $validatedData['siswa_id'],
            'user_id' => $user->id
        ]);

        return redirect()->route('data-orang-tua.index')->with('success', 'Data orang tua berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $siswas = Siswa::all();
        $orangTua = OrangTua::with('user')->findOrFail($id); // Eager load user
        $user = $orangTua->user; // Get the related user
        $statuss = ['Anggota', 'Pengurus Paguyuban Kelas', 'Pengurus Paguyuban Besar'];
        return view('admin.orang_tua.edit', compact('orangTua', 'siswas', 'statuss', 'user'));
    }

    public function update(Request $request, $id)
    {
        $orangTua = OrangTua::findOrFail($id);
        $user = User::findOrFail($orangTua->user_id);

        $validatedData = $request->validate([
            'nama' => 'required|string|max:255',
            'no_hp' => 'required|string|max:20',
            'alamat' => 'required|string|max:255',
            'status' => 'required|in:Anggota,Pengurus Paguyuban Kelas,Pengurus Paguyuban Besar',
            'username' => 'nullable|unique:users,username,' . $user->id, // Allow username to be unique except for current user
            'password' => 'nullable|confirmed|min:6', // Password can be null if not changed
            'siswa_id' => 'nullable|exists:siswas,id',
        ]);

        $orangTua->update([
            'nama' => $validatedData['nama'],
            'no_hp' => $validatedData['no_hp'],
            'alamat' => $validatedData['alamat'],
            'status' => $validatedData['status'],
            'siswa_id' => $validatedData['siswa_id']
        ]);

        // Update user data
        if (isset($validatedData['username'])) { // Only update if username is provided in request
            $user->username = $validatedData['username'];
        }
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        return redirect()->route('data-orang-tua.index')->with('success', 'Data orang tua berhasil diubah!');
    }

    public function show($id)
    {
        $orangTua = OrangTua::with(['user', 'siswa'])->findOrFail($id);
        return view('admin.orang_tua.show', compact('orangTua'));
    }

    public function destroy($id)
    {
        $orangTua = OrangTua::findOrFail($id);
        // Delete the associated user
        if ($orangTua->user_id) {
            User::destroy($orangTua->user_id);
        }
        $orangTua->delete();
        return redirect()->route('data-orang-tua.index')->with('success', 'Data orang tua berhasil dihapus!');
    }

    // --- Import Functions ---

    public function importForm()
    {
        return view('admin.orang_tua.import'); // This will load your import view
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        try {
            Excel::import(new OrangTuaImport, $request->file('file'));
            return redirect()->route('data-orang-tua.index')->with('success', 'Data orang tua berhasil diimpor!');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errorMessage = "Gagal mengimpor data. Beberapa baris memiliki kesalahan: <br>";
            foreach ($failures as $failure) {
                $errorMessage .= "Baris " . $failure->row() . ": ";
                foreach ($failure->errors() as $error) {
                    $errorMessage .= $error . " ";
                }
                $errorMessage .= "<br>";
            }
            return redirect()->back()->with('error', $errorMessage)->withInput();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage())->withInput();
        }
    }

    // public function downloadTemplate()
    // {
    //     // You'll need to create app/Exports/OrangTuaTemplateExport.php
    //     return Excel::download(new OrangTuaTemplateExport, 'template_import_orang_tua.xlsx');
    // }
}