<?php

namespace App\Http\Controllers\Pembina;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\NilaiNonAkademik;
use App\Models\Siswa;
use Illuminate\Support\Facades\Auth;

class ManajemenNilaiNonAkademik extends Controller
{
    public function index()
    {
        // Ambil pembina yang sedang login
        $pembina = Auth::user()->pembina;

        $nilaiNonAkademiks = NilaiNonAkademik::where('pembina_id', $pembina->id)
                                    ->with(['siswa', 'pembina'])
                                    ->latest()
                                    ->get();

        return view('pembina.nilai_non_akademik.index', compact('nilaiNonAkademiks', 'pembina'));
    }

    public function create()
    {
        $pembina = Auth::user()->pembina;
        $siswas = Siswa::all(); // Ambil semua siswa untuk dropdown
        $semesters = ['semester 1', 'semester 2', 'semester 3', 'semester 4', 'semester 5', 'semester 6'];

        return view('pembina.nilai_non_akademik.create', compact('siswas', 'pembina', 'semesters'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kategori' => 'required|string|max:255',
            'semester' => 'required|in:semester 1,semester 2,semester 3,semester 4,semester 5,semester 6',
            'nilai' => 'required|numeric|min:0|max:100',
            'siswa_id' => 'required|exists:siswas,id',
        ]);

        NilaiNonAkademik::create([
            'kategori' => $request->kategori,
            'semester' => $request->semester,
            'nilai' => $request->nilai,
            'siswa_id' => $request->siswa_id,
            'pembina_id' => Auth::user()->pembina->id, // Otomatis mengisi pembina_id dengan pembina yang login
        ]);

        return redirect()->route('nilai_non_akademik.index')->with('success', 'Nilai non akademik berhasil ditambahkan!');
    }

    public function edit(NilaiNonAkademik $nilaiNonAkademik)
    {
        // Pastikan pembina yang login memiliki hak untuk mengedit nilai ini
        if ($nilaiNonAkademik->pembina_id !== Auth::user()->pembina->id) {
            return redirect()->route('nilai_non_akademik.index')->with('error', 'Anda tidak memiliki akses untuk mengedit nilai ini.');
        }

        $siswas = Siswa::all();
        $pembina = Auth::user()->pembina;
        $semesters = ['semester 1', 'semester 2', 'semester 3', 'semester 4', 'semester 5', 'semester 6'];

        return view('pembina.nilai_non_akademik.edit', compact('nilaiNonAkademik', 'siswas', 'pembina', 'semesters'));
    }

    public function update(Request $request, NilaiNonAkademik $nilaiNonAkademik)
    {
        // Pastikan pembina yang login memiliki hak untuk mengupdate nilai ini
        if ($nilaiNonAkademik->pembina_id !== Auth::user()->pembina->id) {
            return redirect()->route('nilai_non_akademik.index')->with('error', 'Anda tidak memiliki akses untuk memperbarui nilai ini.');
        }

        $request->validate([
            'kategori' => 'required|string|max:255',
            'semester' => 'required|in:semester 1,semester 2,semester 3,semester 4,semester 5,semester 6',
            'nilai' => 'required|numeric|min:0|max:100',
            'siswa_id' => 'required|exists:siswas,id',
        ]);

        $nilaiNonAkademik->update([
            'kategori' => $request->kategori,
            'semester' => $request->semester,
            'nilai' => $request->nilai,
            'siswa_id' => $request->siswa_id,
        ]);

        return redirect()->route('nilai_non_akademik.index')->with('success', 'Nilai non akademik berhasil diperbarui!');
    }

    public function destroy(NilaiNonAkademik $nilaiNonAkademik)
    {
        // Pastikan pembina yang login memiliki hak untuk menghapus nilai ini
        if ($nilaiNonAkademik->pembina_id !== Auth::user()->pembina->id) {
            return redirect()->route('nilai_non_akademik.index')->with('error', 'Anda tidak memiliki akses untuk menghapus nilai ini.');
        }

        $nilaiNonAkademik->delete();

        return redirect()->route('nilai_non_akademik.index')->with('success', 'Nilai non akademik berhasil dihapus!');
    }
}
