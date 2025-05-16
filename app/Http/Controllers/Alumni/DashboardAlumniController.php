<?php

namespace App\Http\Controllers\Alumni;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Alumni;
use App\Models\Pembina;
use App\Models\Guru;

class DashboardAlumniController extends Controller
{
    public function index()
    {
        $jumlahSiswa = Siswa::count();
        $jumlahGuru = Guru::count();
        $jumlahAlumni = Alumni::count();
        $jumlahPembina = Pembina::count();
        return view('alumni.dashboard_alumni', compact('jumlahSiswa', 'jumlahPembina', 'jumlahGuru', 'jumlahAlumni'));
    }
}
