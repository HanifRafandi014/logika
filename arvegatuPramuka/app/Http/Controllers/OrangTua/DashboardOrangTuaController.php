<?php

namespace App\Http\Controllers\OrangTua;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\NilaiNonAkademik; // Import model NilaiNonAkademik
use App\Models\OrangTua; // Pastikan model OrangTua sudah ada
use App\Models\NilaiAkademik; // Pastikan model NilaiAkademik sudah ada
use App\Models\Siswa; // Pastikan model Siswa sudah ada

class DashboardOrangTuaController extends Controller
{
    public function index(Request $request) // Terima objek Request
    {
        // Ambil user yang sedang login
        $user = Auth::user();

        // Ambil semester yang dipilih dari request, default null jika tidak ada
        $selectedSemester = $request->input('semester');

        // Inisialisasi variabel untuk data akademik
        $studentName = 'Data Siswa Tidak Ditemukan';
        $academicLabels = [];
        $academicData = [];

        // Inisialisasi variabel untuk data non-akademik
        $nonAcademicLabels = [];
        $nonAcademicData = [];

        $orangTua = $user->orang_tua;

        if ($orangTua) {
            $siswa = $orangTua->siswa;

            if ($siswa) {
                $studentName = $siswa->nama;

                // --- Data Nilai Akademik ---
                $nilaiAkademiksQuery = NilaiAkademik::where('siswa_id', $siswa->id);

                // Terapkan filter semester jika ada
                if ($selectedSemester) {
                    $nilaiAkademiksQuery->where('semester', $selectedSemester);
                }

                $nilaiAkademiks = $nilaiAkademiksQuery->select('mata_pelajaran', DB::raw('AVG(nilai) as average_nilai'))
                                                      ->groupBy('mata_pelajaran')
                                                      ->get();

                // Siapkan data untuk Chart.js dari data akademik
                foreach ($nilaiAkademiks as $nilai) {
                    $academicLabels[] = $nilai->mata_pelajaran;
                    $academicData[] = round($nilai->average_nilai, 2);
                }

                // --- Data Nilai Non Akademik ---
                $nilaiNonAkademiksQuery = NilaiNonAkademik::where('siswa_id', $siswa->id);

                // Terapkan filter semester jika ada
                if ($selectedSemester) {
                    $nilaiNonAkademiksQuery->where('semester', $selectedSemester);
                }

                $nilaiNonAkademiks = $nilaiNonAkademiksQuery->select('kategori', DB::raw('AVG(nilai) as average_nilai'))
                                                              ->groupBy('kategori')
                                                              ->get();

                // Siapkan data untuk Chart.js dari data non-akademik
                foreach ($nilaiNonAkademiks as $nilai) {
                    $nonAcademicLabels[] = $nilai->kategori;
                    $nonAcademicData[] = round($nilai->average_nilai, 2);
                }

            } else {
                $studentName = 'Siswa Belum Terhubung dengan Orang Tua ini';
            }
        } else {
            $studentName = 'Orang Tua Belum Terdaftar untuk Akun ini';
        }

        // Mengirim semua data ke view, termasuk semester yang dipilih
        return view('orang_tua.dashboard_orang_tua', compact(
            'studentName',
            'academicLabels',
            'academicData',
            'nonAcademicLabels',
            'nonAcademicData',
            'selectedSemester' // Kirim semester yang dipilih ke view
        ));
    }
}
