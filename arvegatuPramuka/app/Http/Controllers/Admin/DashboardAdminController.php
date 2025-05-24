<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Alumni;
use App\Models\Pembina;
use App\Models\Guru;

class DashboardAdminController extends Controller
{
    public function index()
    {
        $jumlahSiswa = Siswa::count();
        $jumlahGuru = Guru::count();
        $jumlahAlumni = Alumni::count();
        $jumlahPembina = Pembina::count();
        return view('admin.dashboard_admin', compact('jumlahSiswa', 'jumlahPembina', 'jumlahGuru', 'jumlahAlumni'));
    }
}
