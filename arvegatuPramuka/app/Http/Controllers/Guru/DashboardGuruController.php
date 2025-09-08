<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Alumni;
use App\Models\Pembina;
use App\Models\Guru;

class DashboardGuruController extends Controller
{
    public function index()
    {
        $jumlahSiswa = Siswa::count();
        $jumlahGuru = Guru::count();
        $jumlahAlumni = Alumni::count();
        $jumlahPembina = Pembina::count();
        return view('guru.dashboard_guru', compact('jumlahSiswa', 'jumlahPembina', 'jumlahGuru', 'jumlahAlumni'));
    }
}
