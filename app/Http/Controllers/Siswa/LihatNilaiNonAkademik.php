<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LihatNilaiNonAkademik extends Controller
{
    public function index()
    {
        return view('siswa.lihat_nilai_non_akademik');
    }
}
