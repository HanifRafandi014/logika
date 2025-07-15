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
use App\Http\Controllers\Admin\ManajemenSkuController; 
use App\Http\Controllers\Admin\ManajemenSkkController; 
use App\Http\Controllers\Admin\BesaranBiayaController; 
use App\Http\Controllers\Admin\VariabelController;
// Pembina Controllers
use App\Http\Controllers\Pembina\EditProfilPembinaController;
use App\Http\Controllers\Pembina\DashboardPembinaController;
use App\Http\Controllers\Pembina\RecommendationController;
use App\Http\Controllers\Pembina\ProfilPembinaController;
use App\Http\Controllers\Pembina\ManajemenNilaiNonAkademik;
use App\Http\Controllers\Pembina\LombaController;
use App\Http\Controllers\Pembina\PenilaianSkkController;
use App\Http\Controllers\Pembina\PenilaianSkuController;
use App\Http\Controllers\Pembina\PencapaianSkuSkkController;
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
    Route::resource('manajemen_sku', ManajemenSkuController::class); 
    Route::resource('manajemen_skk', ManajemenSkkController::class); 
    Route::resource('data-besaran-biaya', BesaranBiayaController::class);
    Route::resource('data-variabel', VariabelController::class);

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
        Route::get('/data-pembina/export', [PembinaController::class, 'pembinaExport'])->name('admin.pembina.data-pembina.export');
    });
    Route::prefix('guru')->group(function () {
        Route::resource('/data-guru', GuruController::class);
        Route::get('/import-form-guru', [GuruController::class, 'importForm'])->name('admin.guru.import-form-guru');
        Route::post('/import-guru', [GuruController::class, 'import'])->name('admin.guru.import-guru');
        Route::get('/data-guru/download-template', [GuruController::class, 'downloadTemplate'])->name('admin.guru.download-template');
    });
    Route::prefix('orang-tua')->group(function () {
        Route::resource('/data-orang-tua', OrangTuaController::class);
        Route::get('/import-form-orang-tua', [OrangTuaController::class, 'importForm'])->name('admin.orang_tua.import-form-orang-tua');
        Route::post('/import-orang-tua', [OrangTuaController::class, 'import'])->name('admin.orang_tua.import-orang-tua');
        Route::get('/data-orang-tua/download-template', [OrangTuaController::class, 'downloadTemplate'])->name('admin.orang_tua.download-template');
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

    // Rute baru untuk import nilai akademik
    Route::get('/nilai-akademik/import-form', [ManajemenNilaiAkademik::class, 'showImportForm'])->name('nilai_akademik.show_import_form');
    Route::post('/nilai-akademik/import', [ManajemenNilaiAkademik::class, 'import'])->name('nilai_akademik.import');
});

// --- Rute Pembina ---
Route::prefix('pembina')->middleware(['auth', 'role:pembina'])->group(function () {
    Route::get('/dashboard', [DashboardPembinaController::class, 'index'])->name('pembina.dashboard');
    Route::get('/edit-profil-pembina', [EditProfilPembinaController::class, 'editProfilPembina'])->name('editProfilPembina');
    Route::put('/update-profil-pembina', [EditProfilPembinaController::class, 'updateProfilPembina'])->name('updateProfilPembina');
    Route::get('/rekomendasi', [RecommendationController::class, 'index'])->name('pembina.rekomendasi.index');
    Route::get('/rekomendasi/grafik', [RecommendationController::class, 'grafik'])->name('pembina.rekomendasi.grafik');
    Route::get('/rekomendasi/export', [RecommendationController::class, 'export'])->name('pembina.rekomendasi.export');
    Route::get('/rekomendasi/{lombaSlug}', [RecommendationController::class, 'showByLomba'])->name('pembina.rekomendasi.showByLomba');
    Route::post('/rekomendasi/save/{lombaSlug}', [RecommendationController::class, 'save'])->name('pembina.rekomendasi.save');

    Route::get('/profil-pembina', [ProfilPembinaController::class, 'index'])->name('pembina.profil');
    Route::post('/profil-pembina', [ProfilPembinaController::class, 'update'])->name('pembina.profil.update');
    Route::resource('lomba', LombaController::class);

    // Rute nilai non-akademik
    Route::get('/nilai-non-akademik', [ManajemenNilaiNonAkademik::class, 'index'])->name('nilai_non_akademik.index');
    Route::get('/nilai-non-akademik/create', [ManajemenNilaiNonAkademik::class, 'create'])->name('nilai_non_akademik.create');
    Route::post('/nilai-non-akademik', [ManajemenNilaiNonAkademik::class, 'store'])->name('nilai_non_akademik.store');
    Route::get('/nilai-non-akademik/import-form', [ManajemenNilaiNonAkademik::class, 'showImportForm'])->name('nilai_non_akademik.show_import_form');
    Route::post('/nilai-non-akademik/import', [ManajemenNilaiNonAkademik::class, 'import'])->name('nilai_non_akademik.import');
    Route::get('/nilai-non-akademik/{nilaiNonAkademik}/edit', [ManajemenNilaiNonAkademik::class, 'edit'])->name('nilai_non_akademik.edit'); // Kembali ke metode edit terpisah
    Route::put('/nilai-non-akademik/{nilaiNonAkademik}', [ManajemenNilaiNonAkademik::class, 'update'])->name('nilai_non_akademik.update'); // Kembali ke metode update terpisah
    Route::delete('/nilai-non-akademik/{nilaiNonAkademik}', [ManajemenNilaiNonAkademik::class, 'destroy'])->name('nilai_non_akademik.destroy');

    // Penilaian SKK
    Route::get('/nilai-skk', [PenilaianSkkController::class, 'index'])->name('nilai_skk.index');
    Route::get('/nilai-skk/create', [PenilaianSkkController::class, 'create'])->name('nilai_skk.create');
    Route::post('/nilai-skk', [PenilaianSkkController::class, 'store'])->name('nilai_skk.store');
    Route::get('/nilai-skk/get-skk-items', [PenilaianSkkController::class, 'getSkkItems'])->name('nilai_skk.getSkkItems');
    Route::get('/nilai-skk/student/{siswa_id}', [PenilaianSkkController::class, 'studentAssessments'])->name('nilai_skk.student_assessments');
    Route::get('/nilai-skk/{siswa_id}/{tingkatan}/{jenis_skk}', [PenilaianSkkController::class, 'show'])->name('nilai_skk.show_group');
    Route::get('/nilai-skk/{siswa_id}/{tingkatan}/{jenis_skk}/edit', [PenilaianSkkController::class, 'edit'])->name('nilai_skk.edit_group');
    Route::put('/nilai-skk/{siswa_id}/{tingkatan}/{jenis_skk}', [PenilaianSkkController::class, 'update'])->name('nilai_skk.update_group');
    Route::delete('/nilai-skk/{siswa_id}/{tingkatan}/{jenis_skk}', [PenilaianSkkController::class, 'destroy'])->name('nilai_skk.destroy_group');
    Route::delete('/nilai-skk/delete-all/{siswa_id}', [PenilaianSkkController::class, 'deleteAllForSiswa'])->name('nilai_skk.delete_all_for_siswa');

    // Penilaian SKU
    Route::get('/nilai-sku', [PenilaianSkuController::class, 'index'])->name('nilai_sku.index');
    Route::get('/nilai-sku/create', [PenilaianSkuController::class, 'create'])->name('nilai_sku.create');
    Route::post('/nilai-sku', [PenilaianSkuController::class, 'store'])->name('nilai_sku.store');
    Route::get('/nilai-sku/get-sku-items', [PenilaianSkuController::class, 'getSkuItemsByTingkatan'])->name('nilai_sku.getSkuItemsByTingkatan');
    Route::get('/nilai-sku/student/{siswa_id}', [PenilaianSkuController::class, 'studentAssessments'])->name('nilai_sku.student_assessments');
    Route::get('/nilai-sku/{siswa_id}/{tingkatan}', [PenilaianSkuController::class, 'show'])->name('nilai_sku.show_group');
    Route::get('/nilai-sku/{siswa_id}/{tingkatan}/edit', [PenilaianSkuController::class, 'edit'])->name('nilai_sku.edit_group');
    Route::put('/nilai-sku/{siswa_id}/{tingkatan}', [PenilaianSkuController::class, 'update'])->name('nilai_sku.update_group');
    Route::delete('/nilai-sku/{siswa_id}/{tingkatan}', [PenilaianSkuController::class, 'destroy'])->name('nilai_sku.destroy_group');
    Route::delete('/nilai-sku/delete-all/{siswa_id}', [PenilaianSkuController::class, 'deleteAllForSiswa'])->name('nilai_sku.delete_all_for_siswa');

    Route::get('/lihat-nilai-akademik', [ManajemenNilaiAkademik::class, 'lihatNilaiAkademik'])->name('lihat_nilai.nilai_akademik');
    Route::get('/export-nilai-akademik', [ManajemenNilaiAkademik::class, 'exportNilaiAkademik'])->name('lihat_nilai.export_nilai_akademik');
    Route::get('/lihat-nilai-non-akademik', [ManajemenNilaiNonAkademik::class, 'lihatNilaiNonAkademik'])->name('lihat_nilai.nilai_non_akademik');
    Route::get('/export-nilai-non-akademik', [ManajemenNilaiNonAkademik::class, 'exportNilaiNonAkademik'])->name('lihat_nilai.export_nilai_non_akademik');

    Route::get('/pencapaian-sku', [PencapaianSkuSkkController::class, 'index'])->name('pencapaian-sku.index');
    Route::get('/pencapaian-sku/export', [PencapaianSkuSkkController::class, 'export'])->name('pencapaian-sku.export');
    Route::get('/pencapaian-skk', [PencapaianSkuSkkController::class, 'skkIndex'])->name('pencapaian-skk.index');
    Route::get('/pencapaian-skk/export', [PencapaianSkuSkkController::class, 'skkExport'])->name('pencapaian-skk.export');
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

    // Profil Orang Tua
    Route::get('/edit-profil-orang-tua', [EditProfilOrangTuaController::class, 'editProfilOrangTua'])->name('editProfilOrangTua');
    Route::put('/update-profil-orang-tua', [EditProfilOrangTuaController::class, 'updateProfilOrangTua'])->name('updateProfilOrangTua');
    Route::get('/profile', [DataOrangTua::class, 'showProfileForm'])->name('orang-tua.profile.form');
    Route::post('/profile', [DataOrangTua::class, 'saveOrUpdateProfile'])->name('orang-tua.profile.save-update');
    Route::put('/profile', [DataOrangTua::class, 'saveOrUpdateProfile'])->name('orang-tua.profile.update');

    // Pembayaran Iuran (umum untuk semua orang tua)
    Route::resource('/pembayaran-iuran', PembayaranIuranController::class);
    Route::get('search-siswa', [PembayaranIuranController::class, 'searchSiswa'])->name('search.siswa');
    Route::get('/riwayat-pembayaran', [PembayaranIuranController::class, 'riwayatPembayaran'])->name('orang_tua.pembayaran-iuran.riwayat');
    // Route::get('/riwayat-transaksi-paguyuban-ortu', [PengurusBesarController::class, 'riwayatTransaksiBesar'])->name('orang_tua.riwayat_transaksi_paguyuban_ortu'); // Ini dihapus/dipindahkan

    // Lihat Nilai Siswa
    Route::get('/nilai-siswa', [LihatNilaiSiswaController::class, 'lihatNilaiSiswa'])->name('orang_tua.lihat_nilai_siswa');
    Route::get('/nilai-akademik', [LihatNilaiSiswaController::class, 'lihatNilaiAkademik'])->name('orang_tua.lihat_nilai_akademik');
    Route::get('/nilai-non-akademik', [LihatNilaiSiswaController::class, 'lihatNilaiNonAkademik'])->name('orang_tua.lihat_nilai_non_akademik');

    // Rute untuk Riwayat Transaksi Besar (Dapat diakses oleh seluruh orang tua)
    Route::get('/riwayat-transaksi-besar', [PengurusBesarController::class, 'riwayatTransaksiBesar'])->name('orang_tua.riwayat_transaksi_besar');
    Route::get('/get-detail-pengeluaran/{bulan}', [PengurusBesarController::class, 'getDetailPengeluaranBulanan'])->name('orang_tua.get_detail_pengeluaran');
    Route::get('/export-riwayat-transaksi-besar', [PengurusBesarController::class, 'exportRiwayatTransaksiBesar'])->name('orang_tua.export_riwayat_transaksi_besar');


    Route::prefix('paguyuban-kelas')->middleware(['role:orang_tua,status:Pengurus Paguyuban Kelas'])->group(function () {
        Route::get('/rekapan-setoran', [PengurusKelasController::class, 'rekapanPembayaranKelas'])->name('orang_tua.pengurus_kelas.rekapan_setoran');
        Route::get('/riwayat-pembayaran-kelas', [PengurusKelasController::class, 'rekapanPembayaranKelas'])->name('orang_tua.pengurus_kelas.riwayat_pembayaran_kelas');
        Route::get('/riwayat-pembayaran-kelas/export', [PengurusKelasController::class, 'exportPembayaranKelas'])->name('orang_tua.data-pembayaran-kelas.export');
        Route::get('/setoran-pramuka', [PengurusKelasController::class, 'formSetoran'])->name('orang_tua.pengurus_kelas.form_setoran');
        Route::post('/setoran-pramuka', [PengurusKelasController::class, 'prosesSetoran'])->name('orang_tua.pengurus_kelas.proses_setoran');
        Route::get('/verifikasi-pembayaran', [PembayaranIuranController::class, 'verifikasiIndex'])->name('orang_tua.pengurus_kelas.verifikasi_pembayaran_iuran');
        Route::post('/verifikasi-pembayaran/{id}', [PembayaranIuranController::class, 'verify'])->name('pembayaran-iuran.verify');
        Route::get('/rekapan-setoran/export', [PengurusKelasController::class, 'exportRekapanSetoran'])->name('orang_tua.pengurus_kelas.rekapan_setoran.export');
    });

    Route::prefix('paguyuban-besar')->middleware(['role:orang_tua,status:Pengurus Paguyuban Besar'])->group(function () {
        Route::get('/rekapan-setoran-kelas', [PengurusBesarController::class, 'rekapanSetoranKelas'])->name('orang_tua.pengurus_besar.rekapan_setoran_kelas');
        Route::get('/manajemen-keuangan', [PengurusBesarController::class, 'manajemenKeuangan'])->name('orang_tua.pengurus_besar.manajemen_keuangan');
        Route::post('/pengeluaran', [PengurusBesarController::class, 'storePengeluaran'])->name('orang_tua.pengurus_besar.store_pengeluaran');
        Route::get('/rekapan-setoran-kelas/export', [PengurusBesarController::class, 'exportRekapanSetoranKelas'])->name('orang_tua.pengurus_besar.rekapan_setoran_kelas.export');
        Route::get('verifikasi-setoran', [PengurusKelasController::class, 'verifikasiSetoranIndex'])->name('orang_tua.pengurus_besar.verifikasi_pembayaran_pagu');
        Route::post('verifikasi-setoran/{id}', [PengurusKelasController::class, 'verifySetoran'])->name('pembayaran-setoran.verify');
        Route::get('/riwayat-transaksi-paguyuban', [PengurusBesarController::class, 'riwayatTransaksiPaguyuban'])->name('orang_tua.riwayat_transaksi_paguyuban');
        // Rute-rute riwayat transaksi besar sebelumnya ada di sini, sekarang dipindahkan ke atas
        Route::get('/riwayat-transaksi-paguyuban/export', [PengurusBesarController::class, 'exportRiwayatPaguyubanBesar'])->name('orang_tua.paguyuban_besar.riwayat_paguyuban_besar.export');
    });
});

