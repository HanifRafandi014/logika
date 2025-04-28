<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Admin\AlumniController;
use App\Http\Controllers\Admin\SiswaController;
use App\Http\Controllers\Admin\PembinaController;
use App\Http\Controllers\Admin\GuruController;
use App\Http\Controllers\Admin\OrangTuaController;
use App\Http\Controllers\Admin\DashboardAdminController;
use App\Http\Controllers\Alumni\DashboardAlumniController;
use App\Http\Controllers\Pembina\DashboardPembinaController;
use App\Http\Controllers\Guru\DashboardGuruController;
use App\Http\Controllers\OrangTua\DashboardOrangTuaController;
use App\Http\Controllers\Siswa\DashboardSiswaController;

Route::get('/', function () { 
    return view('auth.login'); 
});
Route::get('/login', function () {
    return view('auth.login');
})->name('login');
Route::post('/login', LoginController::class)->name('login.attempt');
Route::get('/register', function () {
    return view('auth.register');
})->name('register');
Route::post('/register', RegisterController::class)->name('register.attempt');
Route::post('/logout', LogoutController::class)->name('logout');

Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard-admin', [DashboardAdminController::class, 'index'])->name('admin.dashboard_admin');
    Route::prefix('alumni')->group(function () {
        Route::get('/data-alumni', [AlumniController::class, 'index'])->name('admin.alumni.index');
        Route::get('/alumni/{id}', [AlumniController::class, 'show'])->name('admin.alumni.show');
        Route::get('/alumni/{id}/edit', [AlumniController::class, 'edit'])->name('admin.alumni.edit');
        Route::put('/alumni/{id}', [AlumniController::class, 'update'])->name('admin.alumni.update');
        Route::delete('/alumni/{id}', [AlumniController::class, 'destroy'])->name('admin.alumni.destroy');
    });

    Route::prefix('siswa')->group(function () {
        Route::get('/data-siswa', [SiswaController::class, 'index'])->name('admin.siswa.index');
        Route::get('/siswa/{id}', [SiswaController::class, 'show'])->name('admin.siswa.show');
        Route::get('/siswa/{id}/edit', [SiswaController::class, 'edit'])->name('admin.siswa.edit');
        Route::put('/siswa/{id}', [SiswaController::class, 'update'])->name('admin.siswa.update');
        Route::delete('/siswa/{id}', [SiswaController::class, 'destroy'])->name('admin.siswa.destroy');
    });

    Route::prefix('pembina')->group(function () {
        Route::get('/data-pembina', [PembinaController::class, 'index'])->name('admin.pembina.index');
        Route::get('/pembina/{id}', [PembinaController::class, 'show'])->name('admin.pembina.show');
        Route::get('/pembina/{id}/edit', [PembinaController::class, 'edit'])->name('admin.pembina.edit');
        Route::put('/pembina/{id}', [PembinaController::class, 'update'])->name('admin.pembina.update');
        Route::delete('/pembina/{id}', [PembinaController::class, 'destroy'])->name('admin.pembina.destroy');
    });

    Route::prefix('guru')->group(function () {
        Route::get('/data-guru', [GuruController::class, 'index'])->name('admin.guru.index');
        Route::get('/guru/{id}', [GuruController::class, 'show'])->name('admin.guru.show');
        Route::get('/guru/{id}/edit', [GuruController::class, 'edit'])->name('admin.guru.edit');
        Route::put('/guru/{id}', [GuruController::class, 'update'])->name('admin.guru.update');
        Route::delete('/guru/{id}', [GuruController::class, 'destroy'])->name('admin.guru.destroy');
    });

    Route::prefix('orang-tua')->group(function () {
        Route::get('/data-orang-tua', [OrangTuaController::class, 'index'])->name('admin.orang-tua.index');
        Route::get('/orang-tua/{id}', [OrangTuaController::class, 'show'])->name('admin.orang-tua.show');
        Route::get('/orang-tua/{id}/edit', [OrangTuaController::class, 'edit'])->name('admin.orang-tua.edit');
        Route::put('/orang-tua/{id}', [OrangTuaController::class, 'update'])->name('admin.orang-tua.update');
        Route::delete('/orang-tua/{id}', [OrangTuaController::class, 'destroy'])->name('admin.orang-tua.destroy');
    });
});

Route::prefix('alumni')->middleware(['auth', 'role:alumni'])->group(function () {
    Route::get('/dashboard-alumni', [DashboardAlumniController::class, 'index'])->name('alumni.dashboard_alumni');
});

Route::prefix('guru')->middleware(['auth', 'role:guru'])->group(function () {
    Route::get('/dashboard-guru', [DashboardGuruController::class, 'index'])->name('guru.dashboard_guru');
});

Route::prefix('pembina')->middleware(['auth', 'role:pembina'])->group(function () {
    Route::get('/dashboard-pembina', [DashboardPembinaController::class, 'index'])->name('pembina.dashboard_pembina');
});

Route::prefix('siswa')->middleware(['auth', 'role:siswa'])->group(function () {
    Route::get('/dashboard-siswa', [DashboardSiswaController::class, 'index'])->name('siswa.dashboard_siswa');
});

Route::prefix('orang-tua')->middleware(['auth', 'role:orang_tua'])->group(function () {
    Route::get('/dashboard-orang-tua', [DashboardOrangTuaController::class, 'index'])->name('orang-tua.dashboard_orang_tua');
});