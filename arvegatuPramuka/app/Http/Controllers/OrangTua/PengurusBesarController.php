<?php

namespace App\Http\Controllers\OrangTua;

use App\Http\Controllers\Controller;
use App\Models\OrangTua;
use App\Models\SetoranPaguyuban;
use App\Models\TransaksiKeuangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class PengurusBesarController extends Controller
{
    public function rekapanSetoranKelas()
    {
        // Mengambil semua setoran dari kelas, dengan informasi pengurus kelas yang menyetor
        $setoranDariKelas = SetoranPaguyuban::with('pengurus_kelas.siswa') // Load relasi siswa dari pengurus kelas
                                          ->orderBy('bulan_setor', 'desc')
                                          ->get();

        return view('orang_tua.pengurus_besar.rekapan_setoran', compact('setoranDariKelas'));
    }

    public function manajemenKeuangan(Request $request) // Added Request $request
    {
        $pengurusBesar = Auth::user()->orang_tua;

        // Hitung saldo terkini (needed for both views)
        $totalPemasukan = TransaksiKeuangan::where('pengurus_besar_id', $pengurusBesar->id)
                                          ->where('jenis_transaksi', 'pemasukan')
                                          ->sum('jumlah');
        $totalPengeluaran = TransaksiKeuangan::where('pengurus_besar_id', $pengurusBesar->id)
                                            ->where('jenis_transaksi', 'pengeluaran')
                                            ->sum('jumlah');
        $saldoSaatIni = $totalPemasukan - $totalPengeluaran;

        // Riwayat transaksi keuangan (needed for the transaction history view)
        $riwayatTransaksi = TransaksiKeuangan::where('pengurus_besar_id', $pengurusBesar->id)
                                              ->orderBy('tanggal_transaksi', 'desc')
                                              ->get();

        // Determine which view to return based on the current route name
        if ($request->route()->getName() === 'orang_tua.pengurus_besar.manajemen_keuangan') {
            return view('orang_tua.pengurus_besar.manajemen_keuangan', compact('saldoSaatIni'));
        } elseif ($request->route()->getName() === 'orang_tua.pengurus_besar.riwayat_transaksi_keuangan') {
            return view('orang_tua.pengurus_besar.riwayat_transaksi_keuangan', compact('riwayatTransaksi'));
        }

        // Fallback or default view if no specific route name is matched (optional)
        return view('orang_tua.pengurus_besar.manajemen_keuangan', compact('saldoSaatIni', 'riwayatTransaksi'));
    }

    public function storePengeluaran(Request $request)
    {
        $request->validate([
            'jumlah' => 'required|numeric|min:1',
            'kategori' => 'required|string|max:255', // Kategori harus diisi
            'tanggal_transaksi' => 'required|date',
            'bukti_transaksi' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Untuk upload bukti
        ]);

        $pengurusBesar = Auth::user()->orang_tua;
        if (!$pengurusBesar) {
            return redirect()->back()->with('error', 'Data pengurus besar tidak ditemukan.');
        }

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
                'status_pembayaran' => true, // Pengeluaran oleh pengurus besar diasumsikan sudah diverifikasi (opsional)
            ]);

            DB::commit();
            return redirect()->route('orang_tua.pengurus_besar.manajemen_keuangan')->with('success', 'Pengeluaran berhasil dicatat!');

        } catch (\Exception $e) {
            DB::rollBack();
            if ($buktiTransaksiPath) {
                Storage::disk('public')->delete($buktiTransaksiPath);
            }
            return redirect()->back()->withInput()->withErrors(['error' => 'Gagal mencatat pengeluaran: ' . $e->getMessage()]);
        }
    }

    // Metode untuk mengubah status verifikasi setoran
    public function updateSetoranVerification(Request $request, SetoranPaguyuban $setoran)
    {
        $request->validate([
            'status_verifikasi' => 'required|boolean',
        ]);

        $pengurusBesar = Auth::user()->orang_tua;

        // Cek apakah yang login adalah pengurus besar yang terkait
        if (!$pengurusBesar || $setoran->pengurus_besar_id !== $pengurusBesar->id) {
            return response()->json([
                'error' => 'Setoran tidak ditemukan atau Anda tidak memiliki akses.'
            ], 403);
        }

        // Update status verifikasi setoran
        $setoran->status_verifikasi = $request->boolean('status_verifikasi');
        $setoran->save();

        // Update juga status verifikasi pada transaksi keuangan yang terkait
        $transaksi = TransaksiKeuangan::where('pengurus_besar_id', $pengurusBesar->id)
            ->where('jenis_transaksi', 'pemasukan')
            ->where('kategori', 'setoran iuran pramuka')
            ->where('jumlah', $setoran->jumlah)
            ->where('bukti_transaksi', $setoran->bukti_setor)
            ->whereDate('tanggal_transaksi', $setoran->created_at->toDateString()) // diasumsikan tanggal transaksi == created_at
            ->first();

        if ($transaksi) {
            $transaksi->status_pembayaran = $request->boolean('status_verifikasi');
            $transaksi->save();
        }

        return response()->json([
            'success' => 'Status verifikasi setoran berhasil diperbarui.',
            'newStatus' => $setoran->status_verifikasi
        ]);
    }
}