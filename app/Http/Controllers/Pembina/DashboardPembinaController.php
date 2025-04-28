<?php

namespace App\Http\Controllers\Pembina;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardPembinaController extends Controller
{
    public function index()
    {
        return view('pembina.dashboard_pembina');
    }
}
