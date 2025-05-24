<?php

namespace App\Http\Controllers\OrangTua;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Alumni;
use App\Models\Pembina;
use App\Models\Guru;

class DashboardOrangTuaController extends Controller
{
    public function index()
    {
        $jumlahSiswa = Siswa::count();
        $jumlahGuru = Guru::count();
        $jumlahAlumni = Alumni::count();
        $jumlahPembina = Pembina::count();
        return view('orang_tua.dashboard_orang_tua', compact('jumlahSiswa', 'jumlahPembina', 'jumlahGuru', 'jumlahAlumni'));
    }
}
