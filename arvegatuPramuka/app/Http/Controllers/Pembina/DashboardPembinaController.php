<?php

namespace App\Http\Controllers\Pembina;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Alumni;
use App\Models\Pembina;
use App\Models\Guru;

class DashboardPembinaController extends Controller
{
    public function index()
    {
        $jumlahSiswa = Siswa::count();
        $jumlahGuru = Guru::count();
        $jumlahAlumni = Alumni::count();
        $jumlahPembina = Pembina::count();
        return view('pembina.dashboard_pembina', compact('jumlahSiswa', 'jumlahPembina', 'jumlahGuru', 'jumlahAlumni'));
    }
}
