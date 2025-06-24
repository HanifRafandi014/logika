<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alumni;
use App\Models\Siswa;
use App\Models\User; // Import model User
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash; // Import Hash facade
use Illuminate\Support\Str; // Import Str facade untuk string manipulation

class AlumniController extends Controller
{
    public function index()
    {
        // Eager load relasi 'siswa' dan 'user' untuk menghindari N+1 query problem
        $alumnis = Alumni::with('siswa', 'user')->get();
        return view('admin.alumni.index', compact('alumnis'));
    }

    public function create()
    {
        $currentYear = date('Y'); // Tahun saat ini (misal: 2025)
        // Angkatan yang dianggap sebagai alumni: angkatan 2021 atau sebelumnya (jika tahun ini 2025)
        $alumniThresholdAngkatan = $currentYear - 4; 

        // Dan yang belum terdaftar sebagai alumni
        $siswas = Siswa::where('angkatan', '<=', $alumniThresholdAngkatan)
                       ->whereDoesntHave('alumni') // Siswa yang belum menjadi alumni
                       ->get();

        return view('admin.alumni.create', compact('siswas'));
    }

    public function store(Request $request)
    {
        // Validasi input dari form
        $validatedData = $request->validate([
            'siswa_id' => 'required|exists:siswas,id|unique:alumnis,siswa_id', // siswa_id harus ada dan unik di tabel alumnis
            'tahun_lulus' => 'required|integer|min:' . (date('Y') - 5) . '|max:' . (date('Y') + 1), // Contoh validasi tahun lulus
            'pekerjaan' => 'required|string|max:255',
            'no_hp' => 'required|string|max:20',
        ], [
            'siswa_id.unique' => 'Siswa ini sudah terdaftar sebagai alumni.',
        ]);

        // Ambil data siswa berdasarkan siswa_id yang dipilih
        $selectedSiswa = Siswa::findOrFail($validatedData['siswa_id']);

        // Bersihkan nama siswa untuk dijadikan username (misal: "Wika Nurjanah" -> "wikanurjanah")
        $baseUsername = Str::slug($selectedSiswa->nama, ''); // Menghilangkan spasi dan mengubah ke lowercase
        $username = $baseUsername . '1234'; // Contoh: wikanurjanah1234
        $password = Hash::make($baseUsername . '1234'); // Password default: wikanurjanah1234 (hashed)

        // Buat user baru
        $user = User::create([
            'username' => $username, // Username untuk login
            'password' => $password,
            'role' => 'alumni',
        ]);

        // 2. Buat data alumni
        Alumni::create([
            'tahun_lulus' => $validatedData['tahun_lulus'],
            'pekerjaan' => $validatedData['pekerjaan'],
            'no_hp' => $validatedData['no_hp'],
            'siswa_id' => $validatedData['siswa_id'],
            'user_id' => $user->id, // Kaitkan alumni dengan user yang baru dibuat
        ]);

        return redirect()->route('data-alumni.index')->with('success', 'Data alumni dan akun login berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $alumni = Alumni::with('siswa')->findOrFail($id);

        $currentYear = date('Y');
        $alumniThresholdAngkatan = $currentYear - 4;

        // Ambil siswa yang angkatannya sesuai kriteria alumni atau siswa yang saat ini terhubung dengan alumni ini
        $siswas = Siswa::where('angkatan', '<=', $alumniThresholdAngkatan)
                       ->orWhere('id', $alumni->siswa_id) // Sertakan siswa yang saat ini terhubung
                       ->get();

        return view('admin.alumni.edit', compact('alumni', 'siswas'));
    }

    public function update(Request $request, $id)
    {
        $alumni = Alumni::findOrFail($id);

        // Validasi input dari form
        $validatedData = $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'tahun_lulus' => 'required|integer|min:' . (date('Y') - 5) . '|max:' . (date('Y') + 1),
            'pekerjaan' => 'required|string|max:255',
            'no_hp' => 'required|string|max:20',
        ]);

        // Kecuali jika siswa_id yang dipilih adalah siswa_id dari alumni yang sedang diedit
        if ($request->siswa_id != $alumni->siswa_id) {
            $request->validate([
                'siswa_id' => 'unique:alumnis,siswa_id',
            ], [
                'siswa_id.unique' => 'Siswa ini sudah terdaftar sebagai alumni lain.',
            ]);
        }

        // Ambil data siswa yang baru dipilih (jika siswa_id berubah)
        $selectedSiswa = Siswa::findOrFail($validatedData['siswa_id']);

        // Perbarui data alumni
        $alumni->update([
            'tahun_lulus' => $validatedData['tahun_lulus'],
            'pekerjaan' => $validatedData['pekerjaan'],
            'no_hp' => $validatedData['no_hp'],
            'siswa_id' => $validatedData['siswa_id'],
            // user_id tidak diupdate di sini karena sudah dibuat saat create
        ]);

        return redirect()->route('data-alumni.index')->with('success', 'Data alumni berhasil diubah!');
    }

    public function destroy($id)
    {
        $alumni = Alumni::findOrFail($id);

        // Opsional: Jika Anda ingin menghapus user terkait saat alumni dihapus, tambahkan baris ini:
        // if ($alumni->user) {
        //     $alumni->user->delete();
        // }

        $alumni->delete();

        return redirect()->route('data-alumni.index')->with('success', 'Data alumni berhasil dihapus!');
    }
}
