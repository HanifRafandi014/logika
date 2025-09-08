<?php

namespace App\Http\Controllers\OrangTua;

use App\Http\Controllers\Controller;
use App\Models\OrangTua; // Pastikan ini digunakan jika Anda memerlukannya di fungsi lain
use App\Models\SetoranPaguyuban;
use App\Models\TransaksiKeuangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RekapIuranPramukaExport;
use App\Exports\RiwayatTransaksiBesarExport;
use App\Exports\RiwayatPaguyubanBesarExport;

class PengurusBesarController extends Controller
{
    public function rekapanSetoranKelas(): View
    {
        $loggedInUser = Auth::user();
        if (!$loggedInUser || $loggedInUser->role !== 'orang_tua' || !$loggedInUser->orang_tua || $loggedInUser->orang_tua->status !== 'Pengurus Paguyuban Besar') {
            abort(403, 'Unauthorized. Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        $setoranDariKelas = SetoranPaguyuban::with('pengurus_kelas.siswa')
                                           ->orderBy('bulan_setor', 'desc')
                                           ->get();

        return view('orang_tua.pengurus_besar.rekapan_setoran', compact('setoranDariKelas'));
    }

    public function exportRekapanSetoranKelas(Request $request)
    {
        $loggedInUser = Auth::user();
        if (!$loggedInUser || $loggedInUser->role !== 'orang_tua' || !$loggedInUser->orang_tua || $loggedInUser->orang_tua->status !== 'Pengurus Paguyuban Besar') {
            abort(403, 'Unauthorized. Anda tidak memiliki izin untuk melakukan aksi ini.');
        }

        $bulanFilter = $request->query('bulan');
        $fileName = 'Rekapan_Setoran_Iuran_Pramuka_' . Carbon::now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new RekapIuranPramukaExport($bulanFilter), $fileName);
    }

    public function manajemenKeuangan(Request $request): View
    {
        $pengurusBesar = Auth::user()->orang_tua;
        if (!$pengurusBesar || $pengurusBesar->status !== 'Pengurus Paguyuban Besar') {
            abort(403, 'Unauthorized. Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        $totalPemasukan = SetoranPaguyuban::where('pengurus_besar_id', $pengurusBesar->id)->sum('jumlah');
        $totalPengeluaran = TransaksiKeuangan::where('pengurus_besar_id', $pengurusBesar->id)
                                             ->where('jenis_transaksi', 'pengeluaran')
                                             ->sum('jumlah');

        $saldoSaatIni = $totalPemasukan - $totalPengeluaran;
        return view('orang_tua.pengurus_besar.manajemen_keuangan', compact('saldoSaatIni'));
    }

    public function riwayatTransaksiPaguyuban(): View
    {
        $loggedInUser = Auth::user();

        // Optional: Check for role if needed, though view logic already handles it.
        if (!$loggedInUser || $loggedInUser->role !== 'orang_tua' || !$loggedInUser->orang_tua || $loggedInUser->orang_tua->status !== 'Pengurus Paguyuban Besar') {
            abort(403, 'Unauthorized. Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        // Load transactions for the logged-in Pengurus Besar
        // Eager load the 'setoran_paguyaban' relation to access month data
        $riwayatTransaksi = TransaksiKeuangan::with('setoran_paguyuban')
                                         ->where('pengurus_besar_id', $loggedInUser->orang_tua->id)
                                         ->orderBy('tanggal_transaksi', 'desc')
                                         ->get();

        return view('orang_tua.pengurus_besar.riwayat_transaksi_keuangan', compact('riwayatTransaksi'));
    }

    public function storePengeluaran(Request $request)
    {
        $pengurusBesar = Auth::user()->orang_tua;
        if (!$pengurusBesar || $pengurusBesar->status !== 'Pengurus Paguyuban Besar') {
            return redirect()->back()->with('error', 'Unauthorized. Anda tidak memiliki izin untuk melakukan aksi ini.');
        }

        $request->validate([
            'jumlah' => 'required|numeric|min:1',
            'kategori' => 'required|string|max:255',
            'tanggal_transaksi' => 'required|date',
            'bukti_transaksi' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $buktiTransaksiPath = null;
            if ($request->hasFile('bukti_transaksi')) {
                $buktiTransaksiPath = $request->file('bukti_transaksi')->store('bukti_transaksi', 'public');
            }

            TransaksiKeuangan::create([
                'pengurus_besar_id' => $pengurusBesar->id,
                'jenis_transaksi' => 'pengeluaran',
                'kategori' => $request->kategori,
                'jumlah' => $request->jumlah,
                'tanggal_transaksi' => $request->tanggal_transaksi,
                'bukti_transaksi' => $buktiTransaksiPath,
            ]);

            DB::commit();
            return redirect()->route('orang_tua.pengurus_besar.manajemen_keuangan')->with('success', 'Pengeluaran berhasil dicatat!');

        } catch (\Exception $e) {
            DB::rollBack();
            if ($buktiTransaksiPath) {
                Storage::disk('public')->delete($buktiTransaksiPath);
            }
            Log::error('Gagal mencatat pengeluaran: ' . $e->getMessage(), [
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine()
            ]);
            return redirect()->back()->withInput()->withErrors(['error' => 'Gagal mencatat pengeluaran: ' . $e->getMessage()]);
        }
    }

    public function riwayatTransaksiBesar(): View
    {
        // Hanya cek apakah user login dan memiliki role 'orang_tua'
        $loggedInUser = Auth::user();
        if (!$loggedInUser || $loggedInUser->role !== 'orang_tua') {
            abort(403, 'Unauthorized. Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        // Get total pemasukan from all SetoranPaguyuban (assuming this is global for Paguyuban Besar)
        $totalPemasukan = SetoranPaguyuban::sum('jumlah');

        // Get monthly expenditures
        $pengeluaranBulanan = TransaksiKeuangan::selectRaw('MONTH(tanggal_transaksi) as bulan, SUM(jumlah) as total_pengeluaran')
            ->where('jenis_transaksi', 'pengeluaran')
            ->groupByRaw('MONTH(tanggal_transaksi)')
            ->pluck('total_pengeluaran', 'bulan')
            ->toArray();

        $bulanList = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $riwayat = [];
        $saldoBerjalan = $totalPemasukan; // Initialize with total pemasukan

        // Determine the earliest and latest months with *any* transaction for display range
        $minMonthWithTransaction = TransaksiKeuangan::min(DB::raw('MONTH(tanggal_transaksi)'));
        $maxMonthWithTransaction = TransaksiKeuangan::max(DB::raw('MONTH(tanggal_transaksi)'));

        // If there are no transactions at all, show all months with '-'
        if (is_null($minMonthWithTransaction) || is_null($maxMonthWithTransaction)) {
            foreach ($bulanList as $num => $nama) {
                $riwayat[] = [
                    'bulan' => $nama,
                    'bulan_num' => $num, // Add month number for modal
                    'saldo_awal' => '-',
                    'pengeluaran' => '-',
                    'saldo_akhir' => '-'
                ];
            }
        } else {
            foreach ($bulanList as $num => $nama) {
                $pengeluaran = $pengeluaranBulanan[$num] ?? 0;

                // Only calculate saldo if it's within the range of months that have transactions or current month
                if ($num >= $minMonthWithTransaction && $num <= $maxMonthWithTransaction) {
                    $saldoAwal = $saldoBerjalan; // Saldo awal for current month is saldo akhir from previous month
                    $saldoAkhir = $saldoAwal - $pengeluaran;

                    $riwayat[] = [
                        'bulan' => $nama,
                        'bulan_num' => $num, // Add month number for modal
                        'saldo_awal' => $saldoAwal,
                        'pengeluaran' => $pengeluaran,
                        'saldo_akhir' => $saldoAkhir
                    ];
                    $saldoBerjalan = $saldoAkhir; // Update saldo berjalan for the next iteration
                } else {
                    // For months outside the transaction range but within the year
                    $riwayat[] = [
                        'bulan' => $nama,
                        'bulan_num' => $num, // Add month number for modal
                        'saldo_awal' => '-',
                        'pengeluaran' => '-',
                        'saldo_akhir' => '-'
                    ];
                }
            }
        }

        return view('orang_tua.pengurus_besar.riwayat_transaksi_ortu', compact('riwayat'));
    }

    public function getDetailPengeluaranBulanan($bulan)
    {
        // Hanya cek apakah user login dan memiliki role 'orang_tua'
        $loggedInUser = Auth::user();
        if (!$loggedInUser || $loggedInUser->role !== 'orang_tua') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Tidak perlu filter berdasarkan pengurus_besar_id jika ini adalah riwayat umum paguyuban
        $pengeluaranDetails = TransaksiKeuangan::where('jenis_transaksi', 'pengeluaran')
            ->whereMonth('tanggal_transaksi', $bulan)
            ->whereYear('tanggal_transaksi', Carbon::now()->year) // Asumsi tahun saat ini
            ->orderBy('tanggal_transaksi', 'asc')
            ->get(['kategori', 'jumlah', 'tanggal_transaksi']); // Select relevant columns

        return response()->json($pengeluaranDetails);
    }

    public function exportRiwayatTransaksiBesar()
    {
        // Hanya cek apakah user login dan memiliki role 'orang_tua'
        $loggedInUser = Auth::user();
        if (!$loggedInUser || $loggedInUser->role !== 'orang_tua') {
            abort(403, 'Unauthorized. Anda tidak memiliki izin untuk melakukan aksi ini.');
        }

        $fileName = 'Riwayat_Transaksi_Paguyuban_Besar_' . Carbon::now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new RiwayatTransaksiBesarExport(), $fileName);
    }

    public function exportRiwayatPaguyubanBesar()
    {
        // Only check if user is logged in and has 'orang_tua' role and 'Pengurus Paguyuban Besar' status
        $loggedInUser = Auth::user();
        if (!$loggedInUser || $loggedInUser->role !== 'orang_tua' || !$loggedInUser->orang_tua || $loggedInUser->orang_tua->status !== 'Pengurus Paguyuban Besar') {
            // It's safer to check for the specific role and status here as well
            abort(403, 'Unauthorized. Anda tidak memiliki izin untuk melakukan aksi ini.');
        }

        $fileName = 'Riwayat_Transaksi_Paguyuban_Besar_' . Carbon::now()->format('Ymd_His') . '.xlsx';
        
        return Excel::download(new RiwayatPaguyubanBesarExport(), $fileName);
    }
}