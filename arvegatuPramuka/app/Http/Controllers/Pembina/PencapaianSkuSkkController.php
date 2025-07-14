<?php

namespace App\Http\Controllers\Pembina;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PenilaianSku;
use App\Models\Siswa;
use App\Models\ManajemenSku;
use App\Exports\PencapaianSkuExport;
use App\Models\PenilaianSkk;
use App\Models\ManajemenSkk;
use App\Exports\PencapaianSkkExport;
use Maatwebsite\Excel\Facades\Excel;

class PencapaianSkuSkkController extends Controller
{
    public function index()
    {
        // Daftar tingkatan SKU dari tertinggi ke terendah
        $tingkatanOrder = ['Terap', 'Rakit', 'Ramu'];

        $siswas = Siswa::with('pembina')->get();

        $pencapaianFinal = []; // Array untuk menyimpan hasil akhir yang sudah difilter

        foreach ($siswas as $siswa) {
            $siswaPencapaianSku = []; // Menyimpan status kelulusan SKU per tingkatan untuk siswa ini

            foreach ($tingkatanOrder as $tingkatan) {
                $penilaianItems = PenilaianSku::where('siswa_id', $siswa->id)
                    ->where('tingkatan', $tingkatan)
                    ->get();

                $jumlah_dinilai = $penilaianItems->where('status', 1)->count();

                // Hitung jumlah item SKU untuk tingkatan ini
                $total_item = ManajemenSku::where('tingkatan', $tingkatan)->count();

                // Tentukan status kelulusan untuk tingkatan ini
                $statusLulus = ($jumlah_dinilai >= $total_item && $total_item > 0) ? 'Lulus' : 'Belum Lulus';

                // Simpan detail jika siswa lulus tingkatan ini
                if ($statusLulus === 'Lulus') {
                    $siswaPencapaianSku[$tingkatan] = [
                        'nama' => $siswa->nama,
                        'kelas' => $siswa->kelas,
                        'nisn' => $siswa->nisn,
                        'tingkatan' => $tingkatan,
                        'status' => $statusLulus,
                        'tanggal' => $penilaianItems->max('tanggal') ?? '-', // Ambil tanggal terbaru
                    ];
                }
            }

            // Setelah mengecek semua tingkatan untuk siswa ini, tentukan tingkatan tertinggi yang lulus
            $highestLulusTingkatan = null;
            foreach ($tingkatanOrder as $tingkatan) {
                if (isset($siswaPencapaianSku[$tingkatan])) {
                    $highestLulusTingkatan = $siswaPencapaianSku[$tingkatan];
                    break; // Ambil tingkatan tertinggi yang lulus dan keluar dari loop
                }
            }

            // Jika ada tingkatan SKU yang lulus, tambahkan ke daftar akhir
            if ($highestLulusTingkatan) {
                $pencapaianFinal[] = $highestLulusTingkatan;
            }
        }

        return view('pembina.lihat_pencapaian.pencapaian_sku', ['pencapaian' => $pencapaianFinal]);
    }

    public function export()
    {
        return Excel::download(new PencapaianSkuExport, 'pencapaian_sku.xlsx');
    }

    public function skkIndex()
    {
        // Daftar tingkatan SKK dari tertinggi ke terendah
        $tingkatanOrder = ['Utama', 'Madya', 'Purwa'];
        // Ambil semua jenis SKK yang unik dari tabel ManajemenSkk
        $jenisList = ManajemenSkk::select('jenis_skk')->distinct()->pluck('jenis_skk');
        $siswas = Siswa::with('pembina')->get();

        $pencapaianFinal = []; // Array untuk menyimpan hasil akhir yang sudah difilter

        foreach ($siswas as $siswa) {
            foreach ($jenisList as $jenis) {
                $highestLulusTingkatanForJenis = null; // Untuk menyimpan tingkatan tertinggi yang lulus per jenis SKK

                foreach ($tingkatanOrder as $tingkatan) {
                    $penilaianItems = PenilaianSkk::where('siswa_id', $siswa->id)
                        ->where('tingkatan', $tingkatan)
                        ->where('jenis_skk', $jenis)
                        ->get();

                    $jumlah_dinilai = $penilaianItems->where('status', 1)->count();

                    $total_item = ManajemenSkk::where('tingkatan', $tingkatan)
                        ->where('jenis_skk', $jenis)
                        ->count();

                    $statusLulus = ($jumlah_dinilai >= $total_item && $total_item > 0) ? 'Lulus' : 'Belum Lulus';

                    // Jika siswa lulus tingkatan ini untuk jenis SKK ini, simpan dan break
                    if ($statusLulus === 'Lulus') {
                        $highestLulusTingkatanForJenis = [
                            'nama' => $siswa->nama,
                            'kelas' => $siswa->kelas,
                            'nisn' => $siswa->nisn,
                            'jenis_skk' => $jenis,
                            'tingkatan' => $tingkatan,
                            'status' => $statusLulus,
                            'tanggal' => $penilaianItems->max('tanggal') ?? '-',
                        ];
                        break; // Keluar dari loop tingkatan karena sudah menemukan yang tertinggi yang lulus
                    }
                }
                // Jika ada tingkatan yang lulus untuk jenis SKK ini, tambahkan ke array final
                if ($highestLulusTingkatanForJenis) {
                    $pencapaianFinal[] = $highestLulusTingkatanForJenis;
                }
            }
        }

        return view('pembina.lihat_pencapaian.pencapaian_skk', compact('pencapaianFinal'));
    }

    public function skkExport()
    {
        return Excel::download(new PencapaianSkkExport, 'pencapaian_skk.xlsx');
    }
}
