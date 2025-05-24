<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ManajemenNilaiAkademik extends Controller
{
    public function index() {
        $guru = Auth::user()->guru;
    }
}
