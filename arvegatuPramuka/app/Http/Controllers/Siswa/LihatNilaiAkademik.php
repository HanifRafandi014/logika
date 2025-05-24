<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LihatNilaiAkademik extends Controller
{
    public function index()
    {
        return view('siswa.lihat_nilai_akademik');
    }
}
