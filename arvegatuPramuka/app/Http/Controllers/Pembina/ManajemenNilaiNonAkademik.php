<?php

namespace App\Http\Controllers\Pembina;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ManajemenNilaiNonAkademik extends Controller
{
    public function index() {
        $pembina = Auth::user()->pembina;
    }
}
