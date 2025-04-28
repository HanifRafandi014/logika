<?php

namespace App\Http\Controllers\OrangTua;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardOrangTuaController extends Controller
{
    public function index()
    {
        return view('orang_tua.dashboard_orang_tua');
    }
}
