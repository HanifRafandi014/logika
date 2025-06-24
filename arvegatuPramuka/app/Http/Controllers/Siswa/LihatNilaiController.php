<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\NilaiAkademik;
use App\Models\NilaiNonAkademik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LihatNilaiController extends Controller
{
    private function _getSiswaData(Request $request)
    {
        $user = Auth::user();
        $studentName = 'Data Siswa Tidak Ditemukan';
        $siswa = null;

        $siswa = $user->siswa;

        if ($siswa) {
            $studentName = $siswa->nama;
        } else {
            $studentName = 'Akun Pengguna Tidak Terhubung dengan Data Siswa';
        }

        $selectedSemester = $request->input('semester');
        $semesters = ['semester 1', 'semester 2', 'semester 3', 'semester 4', 'semester 5', 'semester 6'];

        return [
            'studentName' => $studentName,
            'siswa' => $siswa,
            'selectedSemester' => $selectedSemester,
            'semesters' => $semesters,
        ];
    }

    public function lihatNilaiSiswa(Request $request)
    {
        return redirect()->route('siswa.lihat_nilai_akademik', ['semester' => $request->input('semester')]);
    }

    public function lihatNilaiAkademik(Request $request)
    {
        $data = $this->_getSiswaData($request);
        $siswa = $data['siswa'];
        $academicGrades = collect(); // Inisialisasi koleksi kosong

        if ($siswa) {
            $academicQuery = NilaiAkademik::where('siswa_id', $siswa->id);
            if ($data['selectedSemester']) {
                $academicQuery->where('semester', $data['selectedSemester']);
            }
            $academicGrades = $academicQuery->get();
        }

        return view('siswa.lihat_nilai_akademik.index', array_merge($data, [
            'academicGrades' => $academicGrades,
        ]));
    }

    public function lihatNilaiNonAkademik(Request $request)
    {
        $data = $this->_getSiswaData($request);
        $siswa = $data['siswa'];
        $nonAcademicGrades = collect(); // Inisialisasi koleksi kosong

        if ($siswa) {
            $nonAcademicQuery = NilaiNonAkademik::where('siswa_id', $siswa->id);
            if ($data['selectedSemester']) {
                $nonAcademicQuery->where('semester', $data['selectedSemester']);
            }
            $nonAcademicGrades = $nonAcademicQuery->get();
        }

        return view('siswa.lihat_nilai_non_akademik.index', array_merge($data, [
            'nonAcademicGrades' => $nonAcademicGrades,
        ]));
    }
}
