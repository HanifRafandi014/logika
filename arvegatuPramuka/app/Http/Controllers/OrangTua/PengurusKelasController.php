<?php

namespace App\Http\Controllers\OrangTua;

use App\Http\Controllers\Controller;
use App\Models\OrangTua;
use App\Models\Siswa;
use App\Models\SetoranPaguyuban;
use App\Models\TransaksiKeuangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class PengurusKelasController extends Controller
{
    public function rekapanPembayaranKelas(Request $request) // Add Request $request
        {
            $orangTua = Auth::user()->orang_tua;
            $kelasSiswa = $orangTua->siswa->kelas; // Mendapatkan kelas siswa dari orang tua

            // Mengambil semua siswa di kelas yang sama
            $siswaDiKelas = Siswa::where('kelas', $kelasSiswa)->pluck('id');

            // Mengambil semua orang tua yang siswanya di kelas yang sama
            $orangTuaDiKelas = OrangTua::whereIn('siswa_id', $siswaDiKelas)->get();

            $dataRekapan = [];
            foreach ($orangTuaDiKelas as $ot) {
                $pembayaran = $ot->pembayaran_spp()->orderBy('bulan_bayar', 'desc')->get(); // Mengambil pembayaran SPP
                $dataRekapan[] = [
                    'siswa_nama' => $ot->siswa->nama ?? 'N/A',
                    'orang_tua_nama' => $ot->nama,
                    'riwayat_pembayaran' => $pembayaran,
                ];
            }

            // Riwayat setoran ke paguyuban besar oleh pengurus kelas ini
            $riwayatSetoran = SetoranPaguyuban::where('pengurus_kelas_id', $orangTua->id)
                                            ->orderBy('bulan_setor', 'desc')
                                            ->get();

            // Determine which view to return based on the current route name
            if ($request->route()->getName() === 'orang_tua.pengurus_kelas.rekapan_setoran') {
                return view('orang_tua.pengurus_kelas.rekapan', compact('riwayatSetoran', 'kelasSiswa'));
            } elseif ($request->route()->getName() === 'orang_tua.pengurus_kelas.riwayat_pembayaran_kelas') {
                return view('orang_tua.pengurus_kelas.riwayat_pembayaran_kelas', compact('dataRekapan', 'kelasSiswa'));
            }

            // Fallback or default view if no specific route name is matched (optional)
            return view('orang_tua.pengurus_kelas.rekapan', compact('dataRekapan', 'riwayatSetoran', 'kelasSiswa'));
    }

    public function formSetoran()
    {
        $orangTua = Auth::user()->orang_tua;
        $kelasSiswa = $orangTua->siswa->kelas;

        // Dapatkan jumlah siswa di kelas (untuk kalkulasi)
        $jumlahSiswaKelas = Siswa::where('kelas', $kelasSiswa)->count();

        // Cari Pengurus Paguyuban Besar untuk tujuan setoran
        $pengurusBesar = OrangTua::where('status', 'Pengurus Paguyuban Besar')->first();
        if (!$pengurusBesar) {
            return redirect()->back()->with('error', 'Tidak ada Pengurus Paguyuban Besar yang terdaftar.');
        }

        return view('orang_tua.pengurus_kelas.form_setoran', compact('jumlahSiswaKelas', 'kelasSiswa', 'pengurusBesar'));
    }

    public function prosesSetoran(Request $request)
    {
        $request->validate([
            'jumlah_siswa' => 'required|integer|min:1',
            'bulan_setor' => 'required|date_format:Y-m', // Format YYYY-MM
            'bukti_setor' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Untuk upload bukti
        ]);

        $orangTua = Auth::user()->orang_tua;
        $kelasSiswa = $orangTua->siswa->kelas;
        $jumlahSiswa = $request->jumlah_siswa;
        $bulanIuranDisetor = Carbon::createFromFormat('Y-m', $request->bulan_setor)->startOfMonth()->toDateString();

        // Kalkulasi jumlah setoran (60% untuk pramuka)
        $jumlahUntukPramuka = $jumlahSiswa * 60000;

        $pengurusBesar = OrangTua::where('status', 'Pengurus Paguyuban Besar')->first();
        if (!$pengurusBesar) {
            return redirect()->back()->with('error', 'Tidak ada Pengurus Paguyuban Besar yang terdaftar untuk setoran.');
        }

        DB::beginTransaction();
        try {
            $buktiSetorPath = null;
            if ($request->hasFile('bukti_setor')) {
                $buktiSetorPath = $request->file('bukti_setor')->store('bukti_setoran', 'public');
            }

            // Catat setoran ke tabel setoran_paguyubans
            SetoranPaguyuban::create([
                'pengurus_kelas_id' => $orangTua->id,
                'pengurus_besar_id' => $pengurusBesar->id,
                'kelas' => $kelasSiswa,
                'bulan_setor' => $bulanIuranDisetor,
                'jumlah' => $jumlahUntukPramuka,
                'bukti_setor' => $buktiSetorPath,
                'status_verifikasi' => false, // Default belum diverifikasi oleh Paguyuban Besar
            ]);

            // Catat pemasukan di Transaksi Keuangan Paguyuban Besar
            TransaksiKeuangan::create([
                'pengurus_besar_id' => $pengurusBesar->id,
                'jenis_transaksi' => 'pemasukan',
                'kategori' => 'setoran iuran pramuka',
                'jumlah' => $jumlahUntukPramuka,
                'bukti_transaksi' => $buktiSetorPath, // Bisa sama dengan bukti setor
                'tanggal_transaksi' => Carbon::now()->toDateString(),
                'status_pembayaran' => false, // Perlu diverifikasi oleh pengurus besar
            ]);

            DB::commit();
            return redirect()->route('orang_tua.pengurus_kelas.rekapan_pembayaran_kelas')->with('success', 'Setoran berhasil dicatat dan menunggu verifikasi Pengurus Paguyuban Besar!');

        } catch (\Exception $e) {
            DB::rollBack();
            // Jika ada file yang terupload, hapus jika transaksi gagal
            if ($buktiSetorPath) {
                Storage::disk('public')->delete($buktiSetorPath);
            }
            return redirect()->back()->withInput()->withErrors(['error' => 'Gagal memproses setoran: ' . $e->getMessage()]);
        }
    }
}
