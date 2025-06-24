<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth; // Pastikan ini ada
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LogoutController;
// Admin Controllers
use App\Http\Controllers\Admin\AlumniController; // Changed alias back to original
use App\Http\Controllers\Admin\SiswaController;
use App\Http\Controllers\Admin\PembinaController;
use App\Http\Controllers\Admin\GuruController;
use App\Http\Controllers\Admin\OrangTuaController;
use App\Http\Controllers\Admin\DashboardAdminController;
use App\Http\Controllers\Admin\EditProfilAdminController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ManajemenSkuController; // Changed alias back to original
use App\Http\Controllers\Admin\ManajemenSkkController; // Changed alias back to original
// Pembina Controllers
use App\Http\Controllers\Pembina\EditProfilPembinaController;
use App\Http\Controllers\Pembina\DashboardPembinaController;
use App\Http\Controllers\Pembina\RecommendationController;
use App\Http\Controllers\Pembina\ProfilPembinaController;
use App\Http\Controllers\Pembina\ManajemenNilaiNonAkademik;
use App\Http\Controllers\Pembina\LombaController;
use App\Http\Controllers\Pembina\PenilaianSkkController;
use App\Http\Controllers\Pembina\PenilaianSkuController;
// Guru Controllers
use App\Http\Controllers\Guru\EditProfilGuruController;
use App\Http\Controllers\Guru\ProfilGuruController;
use App\Http\Controllers\Guru\ManajemenNilaiAkademik;
use App\Http\Controllers\Guru\DashboardGuruController;
// Alumni Controllers
use App\Http\Controllers\Alumni\ManajemenEvent;
use App\Http\Controllers\Alumni\EditProfilAlumniController;
use App\Http\Controllers\Alumni\DashboardAlumniController;
use App\Http\Controllers\Alumni\ProfilAlumniController;
// Orang Tua Controllers
use App\Http\Controllers\OrangTua\EditProfilOrangTuaController;
use App\Http\Controllers\OrangTua\DashboardOrangTuaController;
use App\Http\Controllers\OrangTua\PembayaranIuranController;
use App\Http\Controllers\OrangTua\DataOrangTua;
use App\Http\Controllers\OrangTua\LihatNilaiSiswaController;
use App\Http\Controllers\OrangTua\PengurusKelasController;
use App\Http\Controllers\OrangTua\PengurusBesarController;
// Siswa Controllers
use App\Http\Controllers\Siswa\EditProfilSiswaController;
use App\Http\Controllers\Siswa\DashboardSiswaController;
use App\Http\Controllers\Siswa\LihatNilaiController;

// Rute Default Login dan Register
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

// Rute Dashboard umum (ini akan jarang diakses langsung karena ada redirect di LoginController)
// Tapi bagus untuk fallback atau jika ada logika lain yang ingin Anda tambahkan di sini
Route::get('/dashboard', function() {
    if (Auth::check()) {
        $user = Auth::user();
        switch ($user->role){
            case 'admin': return redirect()->route('admin.dashboard');
            case 'pembina': return redirect()->route('pembina.dashboard');
            case 'guru': return redirect()->route('guru.dashboard');
            case 'siswa' : return redirect()->route('siswa.dashboard');
            case 'orang_tua' : return redirect()->route('orang_tua.dashboard');
            case 'alumni' : return redirect()->route('alumni.dashboard');
            default: return view('welcome'); // Ganti dengan view dashboard default atau error page
        }
    }
    return redirect()->route('login');
})->name('dashboard');


// --- Rute Admin ---
Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [DashboardAdminController::class, 'index'])->name('admin.dashboard'); // URL: /admin/dashboard
    Route::get('/edit-profil-admin', [EditProfilAdminController::class, 'editProfilAdmin'])->name('editProfilAdmin'); // Kembali ke nama asli
    Route::put('/update-profil-admin', [EditProfilAdminController::class, 'updateProfilAdmin'])->name('updateProfilAdmin'); // Kembali ke nama asli
    Route::resource('users', UserController::class);
    Route::resource('manajemen_sku', ManajemenSkuController::class); // Kembali ke nama asli
    Route::resource('manajemen_skk', ManajemenSkkController::class); // Kembali ke nama asli

    // Data Master untuk Admin (dalam grup admin)
    Route::prefix('alumni')->group(function () {
        Route::resource('/data-alumni', AlumniController::class);
    });
    Route::prefix('siswa')->group(function () {
        Route::resource('/data-siswa', SiswaController::class);
        Route::get('/import-form-siswa', [SiswaController::class, 'importForm'])->name('admin.siswa.import-form-siswa');
        Route::post('/import-siswa', [SiswaController::class, 'import'])->name('admin.siswa.import-siswa');
        Route::get('/data-siswa/download-template', [SiswaController::class, 'downloadTemplate'])->name('admin.siswa.download-template');
    });
    Route::prefix('pembina')->group(function () {
        Route::resource('/data-pembina', PembinaController::class);
        Route::get('/import-form-pembina', [PembinaController::class, 'importForm'])->name('admin.pembina.import-form-pembina');
        Route::post('/import-pembina', [PembinaController::class, 'import'])->name('admin.pembina.import-pembina');
        Route::get('/data-pembina/export', [PembinaController::class, 'pembinaExport'])->name('admin.pembina.data-pembina.export'); // Kembali ke nama asli
    });
    Route::prefix('guru')->group(function () {
        Route::resource('/data-guru', GuruController::class);
        Route::get('/import-form-guru', [GuruController::class, 'importForm'])->name('admin.guru.import-form-guru');
        Route::post('/import-guru', [GuruController::class, 'import'])->name('admin.guru.import-guru');
        Route::get('/data-guru/download-template', [GuruController::class, 'downloadTemplate'])->name('admin.guru.download-template');
    });
    Route::prefix('orang-tua')->group(function () {
        Route::resource('/data-orang-tua', OrangTuaController::class);
    });
});

// --- Rute Alumni ---
Route::prefix('alumni')->middleware(['auth', 'role:alumni'])->group(function () {
    Route::get('/dashboard', [DashboardAlumniController::class, 'index'])->name('alumni.dashboard'); // URL: /alumni/dashboard
    Route::get('/edit-profil-alumni', [EditProfilAlumniController::class, 'editProfilAlumni'])->name('editProfilAlumni'); // Kembali ke nama asli
    Route::put('/update-profil-alumni', [EditProfilAlumniController::class, 'updateProfilAlumni'])->name('updateProfilAlumni'); // Kembali ke nama asli
    Route::get('/profil-alumni', [ProfilAlumniController::class, 'index'])->name('alumni.profil'); // Kembali ke nama asli
    Route::post('/profil-alumni', [ProfilAlumniController::class, 'update'])->name('alumni.profil.update'); // Kembali ke nama asli
    Route::resource('/event', ManajemenEvent::class);
});

// --- Rute Guru ---
Route::prefix('guru')->middleware(['auth', 'role:guru'])->group(function () {
    Route::get('/dashboard', [DashboardGuruController::class, 'index'])->name('guru.dashboard'); // URL: /guru/dashboard
    Route::get('/edit-profil-guru', [EditProfilGuruController::class, 'editProfilGuru'])->name('editProfilGuru'); // Kembali ke nama asli
    Route::put('/update-profil-guru', [EditProfilGuruController::class, 'updateProfilGuru'])->name('updateProfilGuru'); // Kembali ke nama asli
    Route::get('/profil', [ProfilGuruController::class, 'index'])->name('guru.profil');
    Route::post('/profil', [ProfilGuruController::class, 'update'])->name('guru.profil.update');

    // Rute nilai akademik (tidak berubah dari yang Anda berikan terakhir)
    Route::get('/nilai-akademik', [ManajemenNilaiAkademik::class, 'index'])->name('nilai_akademik.index');
    Route::get('/nilai-akademik/create', [ManajemenNilaiAkademik::class, 'create'])->name('nilai_akademik.create');
    Route::post('/nilai-akademik', [ManajemenNilaiAkademik::class, 'store'])->name('nilai_akademik.store');
    Route::get('/nilai-akademik/{nilaiAkademik}/edit', [ManajemenNilaiAkademik::class, 'edit'])->name('nilai_akademik.edit');
    Route::put('/nilai-akademik/{nilaiAkademik}', [ManajemenNilaiAkademik::class, 'update'])->name('nilai_akademik.update');
    Route::delete('/nilai-akademik/{nilaiAkademik}', [ManajemenNilaiAkademik::class, 'destroy'])->name('nilai_akademik.destroy');
});

// --- Rute Pembina ---
Route::prefix('pembina')->middleware(['auth', 'role:pembina'])->group(function () {
    Route::get('/dashboard', [DashboardPembinaController::class, 'index'])->name('pembina.dashboard'); // URL: /pembina/dashboard
    Route::get('/edit-profil-pembina', [EditProfilPembinaController::class, 'editProfilPembina'])->name('editProfilPembina'); // Kembali ke nama asli
    Route::put('/update-profil-pembina', [EditProfilPembinaController::class, 'updateProfilPembina'])->name('updateProfilPembina'); // Kembali ke nama asli
    Route::get('/rekomendasi', [RecommendationController::class, 'index'])->name('pembina.rekomendasi.index');
    Route::get('/rekomendasi/{lombaSlug}', [RecommendationController::class, 'showByLomba'])->name('pembina.rekomendasi.showByLomba');
    Route::get('/profil-pembina', [ProfilPembinaController::class, 'index'])->name('pembina.profil');
    Route::post('/profil-pembina', [ProfilPembinaController::class, 'update'])->name('pembina.profil.update');
    Route::resource('lomba', LombaController::class);

    // Rute nilai non-akademik (tidak berubah dari yang Anda berikan terakhir)
    Route::get('/nilai-non-akademik', [ManajemenNilaiNonAkademik::class, 'index'])->name('nilai_non_akademik.index');
    Route::get('/nilai-non-akademik/create', [ManajemenNilaiNonAkademik::class, 'create'])->name('nilai_non_akademik.create');
    Route::post('/nilai-non-akademik', [ManajemenNilaiNonAkademik::class, 'store'])->name('nilai_non_akademik.store');
    Route::get('/nilai-non-akademik/{nilaiNonAkademik}/edit', [ManajemenNilaiNonAkademik::class, 'edit'])->name('nilai_non_akademik.edit');
    Route::put('/nilai-non-akademik/{nilaiNonAkademik}', [ManajemenNilaiNonAkademik::class, 'update'])->name('nilai_non_akademik.update');
    Route::delete('/nilai-non-akademik/{nilaiNonAkademik}', [ManajemenNilaiNonAkademik::class, 'destroy'])->name('nilai_non_akademik.destroy');

    // Penilaian SKK (tidak berubah dari yang Anda berikan terakhir)
    Route::get('/nilai_skk', [PenilaianSkkController::class, 'index'])->name('nilai_skk.index');
    Route::get('/nilai_skk/create', [PenilaianSkkController::class, 'create'])->name('nilai_skk.create');
    Route::post('/nilai_skk', [PenilaianSkkController::class, 'store'])->name('nilai_skk.store');
    Route::get('/nilai_skk/get-skk-items', [PenilaianSkkController::class, 'getSkkItems'])->name('nilai_skk.getSkkItems');
    Route::get('/nilai_skk/{siswa_id}/{tingkatan}/{jenis_skk}/edit', [PenilaianSkkController::class, 'edit'])->name('nilai_skk.edit_group');
    Route::put('/nilai_skk/{siswa_id}/{tingkatan}/{jenis_skk}', [PenilaianSkkController::class, 'update'])->name('nilai_skk.update_group');
    Route::delete('/nilai_skk/{siswa_id}/{tingkatan}/{jenis_skk}', [PenilaianSkkController::class, 'destroy'])->name('nilai_skk.destroy_group');

    // Penilaian SKU (tidak berubah dari yang Anda berikan terakhir)
    Route::get('/nilai_sku', [PenilaianSkuController::class, 'index'])->name('nilai_sku.index');
    Route::get('/nilai_sku/create', [PenilaianSkuController::class, 'create'])->name('nilai_sku.create');
    Route::post('/nilai_sku', [PenilaianSkuController::class, 'store'])->name('nilai_sku.store');
    Route::get('/nilai_sku/get-sku-items', [PenilaianSkuController::class, 'getSkuItemsByTingkatan'])->name('nilai_sku.getSkuItemsByTingkatan');
    Route::get('/nilai_sku/{siswa_id}/{tingkatan}/edit', [PenilaianSkuController::class, 'edit'])->name('nilai_sku.edit_group');
    Route::put('/nilai_sku/{siswa_id}/{tingkatan}', [PenilaianSkuController::class, 'update'])->name('nilai_sku.update_group');
    Route::delete('/nilai_sku/{siswa_id}/{tingkatan}', [PenilaianSkuController::class, 'destroy'])->name('nilai_sku.destroy_group');
});

// --- Rute Siswa ---
Route::prefix('siswa')->middleware(['auth', 'role:siswa'])->group(function () {
    Route::get('/dashboard', [DashboardSiswaController::class, 'index'])->name('siswa.dashboard'); // URL: /siswa/dashboard
    Route::get('/edit-profil-siswa', [EditProfilSiswaController::class, 'editProfilSiswa'])->name('editProfilSiswa'); // Kembali ke nama asli
    Route::put('/update-profil-siswa', [EditProfilSiswaController::class, 'updateProfilSiswa'])->name('updateProfilSiswa'); // Kembali ke nama asli
    // Rute nilai siswa (tidak berubah dari yang Anda berikan terakhir)
    Route::get('/siswa/nilai-siswa', [LihatNilaiController::class, 'lihatNilaiSiswa'])->name('siswa.lihat_nilai_siswa');
    Route::get('/siswa/nilai-akademik', [LihatNilaiController::class, 'lihatNilaiAkademik'])->name('siswa.lihat_nilai_akademik');
    Route::get('/siswa/nilai-non-akademik', [LihatNilaiController::class, 'lihatNilaiNonAkademik'])->name('siswa.lihat_nilai_non_akademik');
});


// --- Rute Orang Tua ---
Route::prefix('orang-tua')->middleware(['auth', 'role:orang_tua'])->group(function () {
    Route::get('/dashboard', [DashboardOrangTuaController::class, 'index'])->name('orang_tua.dashboard');
    Route::get('/edit-profil-orang-tua', [EditProfilOrangTuaController::class, 'editProfilOrangTua'])->name('editProfilOrangTua');
    Route::put('/update-profil-orang-tua', [EditProfilOrangTuaController::class, 'updateProfilOrangTua'])->name('updateProfilOrangTua');

    Route::resource('/pembayaran-iuran', PembayaranIuranController::class);
    Route::get('search-siswa', [PembayaranIuranController::class, 'searchSiswa'])->name('search.siswa');

    Route::get('/riwayat-pembayaran', [PembayaranIuranController::class, 'riwayatPembayaran'])->name('orang_tua.pembayaran-iuran.riwayat');
    Route::get('/profile', [DataOrangTua::class, 'showProfileForm'])->name('orang-tua.profile.form');
    Route::post('/profile', [DataOrangTua::class, 'saveOrUpdateProfile'])->name('orang-tua.profile.save-update');
    Route::put('/profile', [DataOrangTua::class, 'saveOrUpdateProfile'])->name('orang-tua.profile.update');
    Route::get('/orang-tua/nilai-siswa', [LihatNilaiSiswaController::class, 'lihatNilaiSiswa'])->name('orang_tua.lihat_nilai_siswa');
    Route::get('/orang-tua/nilai-akademik', [LihatNilaiSiswaController::class, 'lihatNilaiAkademik'])->name('orang_tua.lihat_nilai_akademik');
    Route::get('/orang-tua/nilai-non-akademik', [LihatNilaiSiswaController::class, 'lihatNilaiNonAkademik'])->name('orang_tua.lihat_nilai_non_akademik');

    Route::prefix('paguyuban-kelas')->middleware('role:orang_tua,status:Pengurus Paguyuban Kelas')->group(function () {
        Route::get('/rekapan-setoran', [PengurusKelasController::class, 'rekapanPembayaranKelas'])->name('orang_tua.pengurus_kelas.rekapan_setoran');
        Route::get('/riwayat-pembayaran-kelas', [PengurusKelasController::class, 'rekapanPembayaranKelas'])->name('orang_tua.pengurus_kelas.riwayat_pembayaran_kelas');
        Route::get('/setoran-pramuka', [PengurusKelasController::class, 'formSetoran'])->name('orang_tua.pengurus_kelas.form_setoran');
        Route::post('/setoran-pramuka', [PengurusKelasController::class, 'prosesSetoran'])->name('orang_tua.pengurus_kelas.proses_setoran');
    });

    Route::prefix('paguyuban-besar')->middleware('role:orang_tua,status:Pengurus Paguyuban Besar')->group(function () {
        Route::get('/rekapan-setoran-kelas', [PengurusBesarController::class, 'rekapanSetoranKelas'])->name('orang_tua.pengurus_besar.rekapan_setoran_kelas');
        Route::get('/manajemen-keuangan', [PengurusBesarController::class, 'manajemenKeuangan'])->name('orang_tua.pengurus_besar.manajemen_keuangan');
        Route::get('/riwayat-transaksi-keuangan', [PengurusBesarController::class, 'manajemenKeuangan'])->name('orang_tua.pengurus_besar.riwayat_transaksi_keuangan');
        Route::post('/pengeluaran', [PengurusBesarController::class, 'storePengeluaran'])->name('orang_tua.pengurus_besar.store_pengeluaran');
        Route::put('/setoran/{setoranPaguyuban}/verify', [PengurusBesarController::class, 'updateSetoranVerification'])->name('orang_tua.pengurus_besar.verify_setoran');
    });
});