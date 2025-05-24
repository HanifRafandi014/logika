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
use App\Http\Controllers\Admin\EditProfilAdminController;
use App\Http\Controllers\Pembina\EditProfilPembinaController;
use App\Http\Controllers\Guru\EditProfilGuruController;
use App\Http\Controllers\Guru\ProfilGuruController;
use App\Http\Controllers\Alumni\EditProfilAlumniController;
use App\Http\Controllers\OrangTua\EditProfilOrangTuaController;
use App\Http\Controllers\Siswa\EditProfilSiswaController;
use App\Http\Controllers\Admin\DashboardAdminController;
use App\Http\Controllers\Alumni\DashboardAlumniController;
use App\Http\Controllers\Pembina\DashboardPembinaController;
use App\Http\Controllers\Guru\DashboardGuruController;
use App\Http\Controllers\Admin\UserController;
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
    Route::get('/edit-profil-admin', [EditProfilAdminController::class, 'editProfilAdmin'])->name('editProfilAdmin');
    Route::put('/update-profil-admin', [EditProfilAdminController::class, 'updateProfilAdmin'])->name('updateProfilAdmin');
    Route::resource('users', UserController::class);
    Route::get('/dashboard-admin', [DashboardAdminController::class, 'index'])->name('admin.dashboard');
    Route::prefix('alumni')->group(function () {
        Route::resource('/data-alumni', AlumniController::class);
    });

    Route::prefix('siswa')->group(function () {
        Route::resource('/data-siswa', SiswaController::class);
        Route::get('/import-form-siswa', [SiswaController::class, 'importForm'])->name('admin.siswa.import-form-siswa');
        Route::post('/import-siswa', [SiswaController::class, 'import'])->name('admin.siswa.import-siswa');
    });

    Route::prefix('pembina')->group(function () {
        Route::resource('/data-pembina', PembinaController::class);
        Route::get('/import-form-pembina', [PembinaController::class, 'importForm'])->name('admin.pembina.import-form-pembina');
        Route::post('/import-pembina', [PembinaController::class, 'import'])->name('admin.pembina.import-pembina');
    });

    Route::prefix('guru')->group(function () {
        Route::resource('/data-guru', GuruController::class);
        Route::get('/import-form-guru', [GuruController::class, 'importForm'])->name('admin.guru.import-form-guru');
        Route::post('/import-guru', [GuruController::class, 'import'])->name('admin.guru.import-guru');
    });

    Route::prefix('orang-tua')->group(function () {
        Route::resource('/data-orang-tua', OrangTuaController::class);
    });

    Route::prefix('alumni')->group(function () {
        Route::resource('/data-alumni', AlumniController::class);
    });
});

Route::prefix('alumni')->middleware(['auth', 'role:alumni'])->group(function () {
    Route::get('/dashboard-alumni', [DashboardAlumniController::class, 'index'])->name('alumni.dashboard');
    Route::get('/edit-profil-alumni', [EditProfilAlumniController::class, 'editProfilAlumni'])->name('editProfilAlumni');
    Route::put('/update-profil-alumni', [EditProfilAlumniController::class, 'updateProfilAlumni'])->name('updateProfilAlumni');
});

Route::prefix('guru')->middleware(['auth', 'role:guru'])->group(function () {
    Route::get('/dashboard-guru', [DashboardGuruController::class, 'index'])->name('guru.dashboard');
    Route::get('/edit-profil-guru', [EditProfilGuruController::class, 'editProfilGuru'])->name('editProfilGuru');
    Route::put('/update-profil-guru', [EditProfilGuruController::class, 'updateProfilGuru'])->name('updateProfilGuru');
    Route::get('/profil', [ProfilGuruController::class, 'index'])->name('guru.profil');
    Route::post('/profil', [ProfilGuruController::class, 'update'])->name('guru.profil.update');
});

Route::prefix('pembina')->middleware(['auth', 'role:pembina'])->group(function () {
    Route::get('/dashboard-pembina', [DashboardPembinaController::class, 'index'])->name('pembina.dashboard');
    Route::get('/edit-profil-pembina', [EditProfilPembinaController::class, 'editProfilPembina'])->name('editProfilPembina');
    Route::put('/update-profil-pembina', [EditProfilPembinaController::class, 'updateProfilPembina'])->name('updateProfilPembina');
});

Route::prefix('siswa')->middleware(['auth', 'role:siswa'])->group(function () {
    Route::get('/dashboard-siswa', [DashboardSiswaController::class, 'index'])->name('siswa.dashboard');
    Route::get('/edit-profil-siswa', [EditProfilSiswaController::class, 'editProfilSiswa'])->name('editProfilSiswa');
    Route::put('/update-profil-siswa', [EditProfilSiswaController::class, 'updateProfilSiswa'])->name('updateProfilSiswa');
});

Route::prefix('orang-tua')->middleware(['auth', 'role:orang_tua'])->group(function () {
    Route::get('/dashboard-orang-tua', [DashboardOrangTuaController::class, 'index'])->name('orang-tua.dashboard');
    Route::get('/edit-profil-orang-tua', [EditProfilOrangTuaController::class, 'editProfilOrangTua'])->name('editProfilOrangTua');
    Route::put('/update-profil-orang-tua', [EditProfilOrangTuaController::class, 'updateProfilOrangTua'])->name('updateProfilOrangTua');
});