<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\NilaiAkademik;
use App\Models\Siswa;
use Illuminate\Support\Facades\Auth;

class ManajemenNilaiAkademik extends Controller
{
    public function index()
    {
        // Ambil guru yang sedang login
        $guru = Auth::user()->guru;

        // Ambil nilai akademik yang terkait dengan guru yang login
        $nilaiAkademiks = NilaiAkademik::where('guru_id', $guru->id)
                                    ->with('siswa', 'guru') // Eager load relasi siswa dan guru
                                    ->latest()
                                    ->get();

        return view('guru.nilai_akademik.index', compact('nilaiAkademiks', 'guru'));
    }

    public function create()
    {
        $guru = Auth::user()->guru;
        $siswas = Siswa::all(); // Ambil semua siswa untuk dropdown
        $semesters = ['semester 1', 'semester 2', 'semester 3', 'semester 4', 'semester 5', 'semester 6'];

        return view('guru.nilai_akademik.create', compact('siswas', 'guru', 'semesters'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'mata_pelajaran' => 'required|string|max:255',
            'semester' => 'required|in:semester 1,semester 2,semester 3,semester 4,semester 5,semester 6',
            'nilai' => 'required|numeric|min:0|max:100',
            'siswa_id' => 'required|exists:siswas,id',
        ]);

        NilaiAkademik::create([
            'mata_pelajaran' => $request->mata_pelajaran,
            'semester' => $request->semester,
            'nilai' => $request->nilai,
            'siswa_id' => $request->siswa_id,
            'guru_id' => Auth::user()->guru->id, // Otomatis mengisi guru_id dengan guru yang login
        ]);

        return redirect()->route('nilai_akademik.index')->with('success', 'Nilai akademik berhasil ditambahkan!');
    }

    public function edit(NilaiAkademik $nilaiAkademik)
    {
        // Pastikan guru yang login memiliki hak untuk mengedit nilai ini
        if ($nilaiAkademik->guru_id !== Auth::user()->guru->id) {
            return redirect()->route('nilai_akademik.index')->with('error', 'Anda tidak memiliki akses untuk mengedit nilai ini.');
        }

        $siswas = Siswa::all();
        $guru = Auth::user()->guru;
        $semesters = ['semester 1', 'semester 2', 'semester 3', 'semester 4', 'semester 5', 'semester 6'];

        return view('guru.nilai_akademik.edit', compact('nilaiAkademik', 'siswas', 'guru', 'semesters'));
    }

    public function update(Request $request, NilaiAkademik $nilaiAkademik)
    {
        // Pastikan guru yang login memiliki hak untuk mengupdate nilai ini
        if ($nilaiAkademik->guru_id !== Auth::user()->guru->id) {
            return redirect()->route('nilai_akademik.index')->with('error', 'Anda tidak memiliki akses untuk memperbarui nilai ini.');
        }

        $request->validate([
            'mata_pelajaran' => 'required|string|max:255',
            'semester' => 'required|in:semester 1,semester 2,semester 3,semester 4,semester 5,semester 6',
            'nilai' => 'required|numeric|min:0|max:100',
            'siswa_id' => 'required|exists:siswas,id',
        ]);

        $nilaiAkademik->update([
            'mata_pelajaran' => $request->mata_pelajaran,
            'semester' => $request->semester,
            'nilai' => $request->nilai,
            'siswa_id' => $request->siswa_id,
        ]);

        return redirect()->route('nilai_akademik.index')->with('success', 'Nilai akademik berhasil diperbarui!');
    }

    /**
     * Menghapus nilai akademik dari database.
     */
    public function destroy(NilaiAkademik $nilaiAkademik)
    {
        // Pastikan guru yang login memiliki hak untuk menghapus nilai ini
        if ($nilaiAkademik->guru_id !== Auth::user()->guru->id) {
            return redirect()->route('nilai_akademik.index')->with('error', 'Anda tidak memiliki akses untuk menghapus nilai ini.');
        }

        $nilaiAkademik->delete();

        return redirect()->route('nilai_akademik.index')->with('success', 'Nilai akademik berhasil dihapus!');
    }
}
