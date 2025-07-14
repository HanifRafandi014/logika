<?php

namespace App\Http\Controllers\OrangTua;

use App\Http\Controllers\Controller;
use App\Models\OrangTua;
use App\Models\Siswa;
use App\Models\SetoranPaguyuban;
use App\Models\TransaksiKeuangan;
use App\Models\BesaranBiaya;
use App\Models\PembayaranSpp; // Import PembayaranSpp model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PembayaranKelasExport;
use App\Exports\RekapanSetoranExport;

class PengurusKelasController extends Controller
{
    public function rekapanPembayaranKelas(Request $request): View|RedirectResponse
    {
        $orangTua = Auth::user()->orang_tua;

        if (!$orangTua || !$orangTua->siswa) {
            return redirect()->back()->with('error', 'Akun orang tua ini belum terkait dengan siswa mana pun. Tidak dapat menampilkan rekapan.');
        }

        $kelasSiswa = $orangTua->siswa->kelas;
        
        // Ambil ID siswa di kelas ini
        $siswaIdsInKelas = Siswa::where('kelas', $kelasSiswa)->pluck('id');

        // Mengambil pembayaran terbaru untuk setiap kombinasi siswa_id dan orang_tua_id
        // Pertama, kita ambil ID pembayaran terbaru untuk setiap pasangan siswa_id/orang_tua_id
        $latestPaymentIds = PembayaranSpp::whereIn('siswa_id', $siswaIdsInKelas)
                                          // Opsional: tambahkan filter status_pembayaran jika hanya ingin yang diverifikasi
                                          // ->where('status_pembayaran', 1) 
                                          ->select('siswa_id', 'orang_tua_id', DB::raw('MAX(id) as latest_id'))
                                          ->groupBy('siswa_id', 'orang_tua_id')
                                          ->pluck('latest_id');

        // Kemudian, ambil objek PembayaranSpp lengkap berdasarkan ID terbaru ini
        $riwayatPembayaransUnik = PembayaranSpp::whereIn('id', $latestPaymentIds)
                                            ->with(['siswa', 'orang_tua'])
                                            ->orderBy('created_at', 'desc')
                                            ->get();

        // Data untuk dropdown bulan (masih dibutuhkan untuk filter)
        $bulanDalamTahun = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        // ... (Bagian rekapan setoran lainnya tetap sama, tidak relevan untuk tampilan riwayat pembayaran)
        $riwayatSetoran = SetoranPaguyuban::where('pengurus_kelas_id', $orangTua->id)
            ->orderBy('bulan_setor', 'desc')
            ->get();

        $runningKekurangan = 0;
        foreach ($riwayatSetoran as $setoran) {
            $monthlyShortageOrExcess = $setoran->total - $setoran->jumlah;
            $runningKekurangan += $monthlyShortageOrExcess;
            if ($runningKekurangan < 0) {
                $runningKekurangan = 0;
            }
        }
        $finalRunningKekurangan = $runningKekurangan;


        // Determine which view to load based on the route name
        if ($request->route()->getName() === 'orang_tua.pengurus_kelas.rekapan_setoran') {
            return view('orang_tua.pengurus_kelas.rekapan', compact('riwayatSetoran', 'kelasSiswa', 'finalRunningKekurangan'));
        } elseif ($request->route()->getName() === 'orang_tua.pengurus_kelas.riwayat_pembayaran_kelas') {
            // Kirimkan riwayatPembayaransUnik
            return view('orang_tua.pengurus_kelas.riwayat_pembayaran_kelas', compact('riwayatPembayaransUnik', 'kelasSiswa', 'bulanDalamTahun'));
        }

        // Default view if route name doesn't match
        return view('orang_tua.pengurus_kelas.rekapan', compact('riwayatPembayaransUnik', 'riwayatSetoran', 'kelasSiswa', 'finalRunningKekurangan', 'bulanDalamTahun'));
    }

    public function exportPembayaranKelas(Request $request)
    {
        $orangTua = Auth::user()->orang_tua;

        if (!$orangTua || !$orangTua->siswa) {
            return redirect()->back()->with('error', 'Akun orang tua ini belum terkait dengan siswa mana pun. Tidak dapat mengekspor data.');
        }

        $kelasSiswa = $orangTua->siswa->kelas;
        
        // Ini untuk ekspor, jika Anda ingin ekspor juga hanya data unik per siswa/ortu,
        // Anda harus menerapkan logika yang sama seperti di rekapanPembayaranKelas
        // Jika Anda ingin mengekspor SEMUA transaksi, biarkan seperti ini
        $query = PembayaranSpp::with(['siswa', 'orang_tua'])
            ->whereHas('siswa', function ($q) use ($kelasSiswa) {
                $q->where('kelas', $kelasSiswa);
            })
            ->where('status_pembayaran', 1); // Asumsi ekspor hanya untuk yang sudah diverifikasi

        $monthFilter = $request->query('monthFilter');
        if (!empty($monthFilter)) {
            $monthNames = [
                'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];
            $monthNumber = array_search($monthFilter, $monthNames) + 1;
            
            $query->whereJsonContains('bulan_bayar', $monthNumber);
        }

        $pembayaranToExport = $query->orderBy('created_at', 'desc')->get();

        $fileName = 'rekap_pembayaran_kelas_' . $kelasSiswa;
        if (!empty($monthFilter)) {
            $fileName .= '_' . $monthFilter;
        }
        $fileName .= '_' . Carbon::now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new PembayaranKelasExport($pembayaranToExport), $fileName);
    }

    public function formSetoran(): View|RedirectResponse
    {
        // Check if the user is logged in and has the correct role
        $loggedInUser = Auth::user();
        if (!$loggedInUser || $loggedInUser->role !== 'orang_tua' || $loggedInUser->orang_tua->status !== 'Pengurus Paguyuban Kelas') {
            abort(403, 'Unauthorized. Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        $orangTua = $loggedInUser->orang_tua;

        if (!$orangTua->siswa) {
            return redirect()->back()->with('error', 'Akun pengurus ini belum terkait dengan siswa mana pun.');
        }

        $kelasSiswa = $orangTua->siswa->kelas;
        $jumlahSiswaKelas = Siswa::where('kelas', $kelasSiswa)->count();
        $besaranBiaya = BesaranBiaya::first();

        if (!$besaranBiaya) {
            return redirect()->back()->with('error', 'Data besaran biaya belum tersedia. Harap hubungi admin.');
        }

        // Get the 'Pengurus Paguyuban Besar' to whom the setoran is addressed.
        $pengurusBesar = OrangTua::where('status', 'Pengurus Paguyuban Besar')
            ->orderBy('id', 'desc')
            ->first();

        if (!$pengurusBesar) {
            return redirect()->back()->with('error', 'Tidak ada Pengurus Paguyuban Besar yang terdaftar.');
        }

        $riwayatSetoran = SetoranPaguyuban::where('pengurus_kelas_id', $orangTua->id)
                                         ->whereYear('created_at', now()->year) // Assuming setoran is for current year
                                         ->get();

        // Pre-process setoran history for quick lookup in the view
        $setoranStatusesByMonth = [];
        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        foreach ($riwayatSetoran as $setoran) {
            // $setoran->bulan_setor is automatically cast to array because of $casts property in model
            foreach ($setoran->bulan_setor as $monthName) {
                // Find month number from name
                $monthNumber = array_search($monthName, $monthNames);
                if ($monthNumber !== false) {
                    $setoranStatusesByMonth[$monthNumber] = [
                        'status' => $setoran->status_verifikasi,
                        'setoran_obj' => $setoran // Pass the whole object to access bukti_setor
                    ];
                }
            }
        }
        
        return view('orang_tua.pengurus_kelas.form_setoran', compact('jumlahSiswaKelas', 'kelasSiswa', 'pengurusBesar', 'besaranBiaya', 'setoranStatusesByMonth'));
    }

    public function prosesSetoran(Request $request): RedirectResponse
    {
        // Check if the user is logged in and has the correct role
        $loggedInUser = Auth::user();
        if (!$loggedInUser || $loggedInUser->role !== 'orang_tua' || $loggedInUser->orang_tua->status !== 'Pengurus Paguyuban Kelas') {
            abort(403, 'Unauthorized. Anda tidak memiliki izin untuk melakukan aksi ini.');
        }

        $request->validate([
            'jumlah_siswa' => 'required|integer|min:0',
            'bulan_setor' => 'required|array|min:1', // Now validates as an array of month numbers
            'bulan_setor.*' => 'integer|min:1|max:12', // Each month is a number (1-12)
            'jumlah' => 'required|numeric|min:0',
            'bukti_setor' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'besaran_biaya_id' => 'required|exists:besaran_biayas,id',
        ]);

        $orangTua = $loggedInUser->orang_tua;
        $kelasSiswa = $orangTua->siswa->kelas ?? 'N/A';
        $jumlahSiswa = $request->jumlah_siswa; // This comes from hidden input based on total students in class
        $jumlahYangDisetorkan = $request->jumlah;
        $besaranBiaya = BesaranBiaya::findOrFail($request->besaran_biaya_id);

        $selectedMonthNumbers = $request->bulan_setor;
        $totalYangSeharusnyaDisetor = count($selectedMonthNumbers) * $jumlahSiswa * $besaranBiaya->nominal_pagu_besar;
        $kekurangan = max(0, $totalYangSeharusnyaDisetor - $jumlahYangDisetorkan);
        
        // Convert month numbers to month names for storing in JSON
        $monthNamesMap = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $selectedMonthNames = array_map(function($monthNumber) use ($monthNamesMap) {
            return $monthNamesMap[$monthNumber];
        }, $selectedMonthNumbers);

        // Sort months for consistent storage and display
        sort($selectedMonthNames);
        
        // For TransaksiKeuangan, the transaction date is simply when this form is submitted
        $transactionDate = Carbon::now()->toDateString();

        // Find the 'Pengurus Paguyuban Besar' to link the setoran to
        $pengurusBesar = OrangTua::where('status', 'Pengurus Paguyuban Besar')->orderBy('id', 'desc')->first();
        if (!$pengurusBesar) {
            return redirect()->back()->with('error', 'Tidak ada Pengurus Paguyuban Besar yang terdaftar untuk setoran. Harap hubungi admin.');
        }

        DB::beginTransaction();
        try {
            $buktiSetorPath = null;
            if ($request->hasFile('bukti_setor')) {
                $buktiSetorPath = $request->file('bukti_setor')->store('bukti_setoran', 'public');
            }

            // Create the SetoranPaguyuban entry
            $setoran = SetoranPaguyuban::create([
                'pengurus_kelas_id' => $orangTua->id,
                'pengurus_besar_id' => $pengurusBesar->id, // Assign the big admin's ID
                'kelas' => $kelasSiswa,
                'bulan_setor' => $selectedMonthNames, // Storing as JSON array of month names (auto-casted by model)
                'jumlah' => $jumlahYangDisetorkan,
                'total' => $totalYangSeharusnyaDisetor,
                'bukti_setor' => $buktiSetorPath,
                'kekurangan' => $kekurangan,
                'status_verifikasi' => 0, // Default status: unverified
                'besaran_biaya_id' => $besaranBiaya->id,
            ]);

            // Create the TransaksiKeuangan entry
            TransaksiKeuangan::create([
                'pengurus_besar_id' => $pengurusBesar->id,
                'jenis_transaksi' => 'pemasukan',
                'kategori' => 'setoran iuran kelas',
                'jumlah' => $jumlahYangDisetorkan,
                'bukti_transaksi' => $buktiSetorPath,
                'tanggal_transaksi' => $transactionDate, // Transaction date is today's date
                'setoran_paguyuban_id' => $setoran->id,
            ]);

            DB::commit();
            return redirect()->route('orang_tua.pengurus_kelas.rekapan_setoran')->with('success', 'Setoran berhasil dicatat dan menunggu verifikasi.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($buktiSetorPath) {
                Storage::disk('public')->delete($buktiSetorPath);
            }
            Log::error('Gagal memproses setoran baru: ' . $e->getMessage(), [
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine()
            ]);
            return redirect()->back()->withInput()->with('error', 'Gagal memproses setoran: ' . $e->getMessage());
        }
    }

    // Renamed this method to avoid confusion. This is for Pengurus Besar to verify setoran from Pengurus Kelas.
    public function verifikasiSetoranIndex(Request $request): View|RedirectResponse
    {
        $loggedInUser = Auth::user();

        // Check if the logged-in user is an 'orang_tua' with 'Pengurus Paguyuban Besar' status
        if (!$loggedInUser || $loggedInUser->role !== 'orang_tua' || !$loggedInUser->orang_tua || $loggedInUser->orang_tua->status !== 'Pengurus Paguyuban Besar') {
            abort(403, 'Unauthorized. Anda tidak memiliki izin untuk mengakses halaman verifikasi setoran paguyuban.');
        }

        $pengurusBesarId = $loggedInUser->orang_tua->id;
        
        // Start building the query
        $query = SetoranPaguyuban::with(['pengurus_kelas.siswa']) // Eager load siswa for pengurus_kelas
            ->where('status_verifikasi', 0) // Only show unverified setoran
            ->where('pengurus_besar_id', $pengurusBesarId); // Filter by the logged-in admin's ID

        // Check for 'pengurus_kelas_nama' filter from the request
        $pengurusKelasNamaFilter = $request->query('pengurus_kelas_nama');
        if ($pengurusKelasNamaFilter) {
            // Add a filter to the query based on the pengurus_kelas's name
            $query->whereHas('pengurus_kelas', function ($q) use ($pengurusKelasNamaFilter) {
                $q->where('nama', 'like', '%' . $pengurusKelasNamaFilter . '%');
            });
        }

        // Execute the query and get the latest results
        $setoranUntukVerifikasi = $query->latest()->get();

        return view('orang_tua.pengurus_besar.verifikasi_pembayaran_pagu', [
            'setoranUntukVerifikasi' => $setoranUntukVerifikasi,
            'loggedInUser' => $loggedInUser,
            'filteredPengurus' => $pengurusKelasNamaFilter // Pass the filter value to the view
        ]);
    }

    public function verifySetoran(Request $request, int $id): RedirectResponse
    {
        $loggedInUser = Auth::user();

        // Authorization check: only 'Pengurus Paguyuban Besar' can verify
        if (!$loggedInUser || $loggedInUser->role !== 'orang_tua' || !$loggedInUser->orang_tua || $loggedInUser->orang_tua->status !== 'Pengurus Paguyuban Besar') {
            abort(403, 'Unauthorized. Anda tidak memiliki izin untuk memverifikasi setoran.');
        }

        $setoran = SetoranPaguyuban::find($id);

        if (!$setoran) {
            return redirect()->back()->with('error', 'Setoran tidak ditemukan.');
        }
        
        // Check if the setoran is already verified
        if ($setoran->status_verifikasi === 1) {
            return redirect()->back()->with('info', 'Setoran ini sudah diverifikasi sebelumnya.');
        }

        // Ensure the logged-in 'Pengurus Besar' is the one responsible for this setoran
        if ($setoran->pengurus_besar_id !== $loggedInUser->orang_tua->id) {
            return redirect()->back()->with('error', 'Anda tidak berhak memverifikasi setoran ini.');
        }

        try {
            // Update the status to verified
            $setoran->status_verifikasi = 1;
            $setoran->save();

            return redirect()->back()->with('success', 'Setoran berhasil diverifikasi.');
        } catch (\Exception $e) {
            Log::error('Gagal memverifikasi setoran: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memverifikasi setoran: ' . $e->getMessage());
        }
    }

    public function verifikasiPembayaranIuranIndex(Request $request): View|RedirectResponse
    {
        $loggedInUser = Auth::user();

        // Check if the logged-in user is an 'orang_tua' with 'Pengurus Paguyuban Kelas' status
        if (!$loggedInUser || $loggedInUser->role !== 'orang_tua' || !$loggedInUser->orang_tua || $loggedInUser->orang_tua->status !== 'Pengurus Paguyuban Kelas') {
            abort(403, 'Unauthorized. Anda tidak memiliki izin untuk mengakses halaman verifikasi pembayaran iuran.');
        }

        $pengurusKelasOrangTua = $loggedInUser->orang_tua;
        
        if (!$pengurusKelasOrangTua->siswa) {
            return redirect()->back()->with('error', 'Akun pengurus ini belum terkait dengan siswa mana pun.');
        }
        
        $kelasPengurus = $pengurusKelasOrangTua->siswa->kelas;

        // Get all students in the class of the logged-in Pengurus Kelas
        $siswaIdsInKelas = Siswa::where('kelas', $kelasPengurus)->pluck('id');
        
        // Get all OrangTua associated with these students
        $orangTuaIdsInKelas = OrangTua::whereIn('siswa_id', $siswaIdsInKelas)->pluck('id');

        // Build the query for PembayaranIuran
        $query = PembayaranSpp::with(['siswa', 'orang_tua'])
            ->whereIn('orang_tua_id', $orangTuaIdsInKelas)
            ->where('status_pembayaran', 0); // Only show unverified payments

        // Add filter based on orang_tua_id from request (when clicking from riwayat_pembayaran_kelas)
        $filterOrangTuaId = $request->query('orang_tua_id');
        if ($filterOrangTuaId) {
            $query->where('orang_tua_id', $filterOrangTuaId);
        }

        // Add filter based on siswa_id from request (when clicking from riwayat_pembayaran_kelas)
        $filterSiswaId = $request->query('siswa_id');
        if ($filterSiswaId) {
            $query->where('siswa_id', $filterSiswaId);
        }

        // Add filter based on month_name (bulan_bayar) from request
        $filterMonth = $request->query('month');
        if ($filterMonth) {
            $monthNames = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
                7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];
            $monthNumber = array_search($filterMonth, $monthNames);
            if ($monthNumber !== false) {
                   $query->whereJsonContains('bulan_bayar', $monthNumber);
            }
        }
        
        $pembayaranUntukVerifikasi = $query->latest()->get();

        // Month names for display in the view (assuming bulan_bayar stores month numbers)
        $bulanDalamTahun = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        return view('orang_tua.pengurus_kelas.verifikasi_pembayaran_kelas', compact('pembayaranUntukVerifikasi', 'loggedInUser', 'kelasPengurus', 'bulanDalamTahun'));
    }

    /**
     * Verify a specific individual parent payment (for 'Pengurus Paguyuban Kelas' role).
     */
    public function verifyPembayaranIuran(Request $request, int $id): RedirectResponse
    {
        $loggedInUser = Auth::user();

        // Authorization check: only 'Pengurus Paguyuban Kelas' can verify individual payments
        if (!$loggedInUser || $loggedInUser->role !== 'orang_tua' || !$loggedInUser->orang_tua || $loggedInUser->orang_tua->status !== 'Pengurus Paguyuban Kelas') {
            abort(403, 'Unauthorized. Anda tidak memiliki izin untuk memverifikasi pembayaran iuran.');
        }

        $pembayaranIuran = PembayaranSpp::find($id);

        if (!$pembayaranIuran) {
            return redirect()->back()->with('error', 'Pembayaran tidak ditemukan.');
        }
        
        // Check if the payment is already verified
        if ($pembayaranIuran->status_pembayaran === 1) {
            return redirect()->back()->with('info', 'Pembayaran ini sudah diverifikasi sebelumnya.');
        }

        // Ensure the logged-in Pengurus Kelas is verifying payments for their own class
        $pengurusKelasOrangTua = $loggedInUser->orang_tua;
        if (!$pengurusKelasOrangTua->siswa || $pembayaranIuran->siswa->kelas !== $pengurusKelasOrangTua->siswa->kelas) {
            return redirect()->back()->with('error', 'Anda tidak berhak memverifikasi pembayaran untuk kelas lain.');
        }

        DB::beginTransaction();
        try {
            // Update the status to verified
            $pembayaranIuran->status_pembayaran = 1;
            $pembayaranIuran->save();

            TransaksiKeuangan::create([
                'pengurus_kelas_id' => $loggedInUser->orang_tua->id, // Associate with the verifying Pengurus Kelas
                'jenis_transaksi' => 'pemasukan',
                'kategori' => 'pembayaran iuran siswa',
                'jumlah' => $pembayaranIuran->total_bayar_final, // Pastikan ini ada di model PembayaranSpp
                'bukti_transaksi' => $pembayaranIuran->bukti_bayar, // Re-use the payment proof
                'tanggal_transaksi' => Carbon::now()->toDateString(),
                'pembayaran_iuran_id' => $pembayaranIuran->id,
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Pembayaran berhasil diverifikasi.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal memverifikasi pembayaran iuran: ' . $e->getMessage(), [
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine()
            ]);
            return redirect()->back()->with('error', 'Gagal memverifikasi pembayaran: ' . $e->getMessage());
        }
    }

    public function exportRekapanSetoran(): \Symfony\Component\HttpFoundation\BinaryFileResponse|RedirectResponse
    {
        $orangTua = Auth::user()->orang_tua;

        if (!$orangTua || !$orangTua->siswa || $orangTua->status !== 'Pengurus Paguyuban Kelas') {
            return redirect()->back()->with('error', 'Unauthorized. Anda tidak memiliki izin untuk mengekspor rekapan setoran.');
        }

        $pengurusKelasId = $orangTua->id;
        $kelasSiswa = $orangTua->siswa->kelas;

        $fileName = 'rekapan_setoran_kelas_' . $kelasSiswa . '_' . Carbon::now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new RekapanSetoranExport($pengurusKelasId), $fileName);
    }
}