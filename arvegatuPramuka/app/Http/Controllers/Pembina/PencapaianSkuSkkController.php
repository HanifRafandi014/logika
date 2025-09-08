<?php

namespace App\Http\Controllers\Pembina;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PenilaianSku;
use App\Models\Siswa;
use App\Models\ManajemenSku;
use App\Exports\PencapaianSkuExport;
use Illuminate\Support\Facades\Auth;
use App\Models\PenilaianSkk;
use App\Models\ManajemenSkk;
use App\Exports\PencapaianSkkExport;
use Maatwebsite\Excel\Facades\Excel;

class PencapaianSkuSkkController extends Controller
{
    public function index()
    {
        if (!Auth::check() || Auth::user()->role !== 'pembina') {
            return redirect()->route('login')->with('error', 'Anda harus login sebagai pembina.');
        }

        $pembina = Auth::user()->pembina;
        if (!$pembina) {
            return redirect()->route('dashboard')->with('error', 'Data pembina Anda tidak ditemukan.');
        }

        $siswas = Siswa::with('pembina')
            ->where('kelas', $pembina->kelas)
            ->get();

        $tingkatanOrder = ['Terap', 'Rakit', 'Ramu'];
        $pencapaianFinal = [];

        foreach ($siswas as $siswa) {
            $siswaPencapaianSku = [];

            foreach ($tingkatanOrder as $tingkatan) {
                $penilaianItems = PenilaianSku::where('siswa_id', $siswa->id)
                    ->where('tingkatan', $tingkatan)
                    ->get();

                $jumlah_dinilai = $penilaianItems->where('status', 1)->count();
                $total_item = ManajemenSku::where('tingkatan', $tingkatan)->count();

                $statusLulus = ($jumlah_dinilai >= $total_item && $total_item > 0) ? 'Lulus' : 'Belum Lulus';

                if ($statusLulus === 'Lulus') {
                    $siswaPencapaianSku[$tingkatan] = [
                        'nama' => $siswa->nama,
                        'kelas' => $siswa->kelas,
                        'nisn' => $siswa->nisn,
                        'tingkatan' => $tingkatan,
                        'status' => $statusLulus,
                        'tanggal' => $penilaianItems->max('tanggal') ?? '-',
                    ];
                }
            }

            $highestLulusTingkatan = null;
            foreach ($tingkatanOrder as $tingkatan) {
                if (isset($siswaPencapaianSku[$tingkatan])) {
                    $highestLulusTingkatan = $siswaPencapaianSku[$tingkatan];
                    break;
                }
            }

            if ($highestLulusTingkatan) {
                $pencapaianFinal[] = $highestLulusTingkatan;
            }
        }

        return view('pembina.lihat_pencapaian.pencapaian_sku', ['pencapaian' => $pencapaianFinal]);
    }

    public function export()
    {
        $pembina = Auth::user()->pembina;
        return Excel::download(new PencapaianSkuExport($pembina->kelas), 'pencapaian_sku.xlsx');
    }

    public function skkIndex()
    {
        if (!Auth::check() || Auth::user()->role !== 'pembina') {
            return redirect()->route('login')->with('error', 'Anda harus login sebagai pembina.');
        }

        $pembina = Auth::user()->pembina;
        if (!$pembina) {
            return redirect()->route('dashboard')->with('error', 'Data pembina Anda tidak ditemukan.');
        }

        $siswas = Siswa::with('pembina')
            ->where('kelas', $pembina->kelas)
            ->get();

        $tingkatanOrder = ['Utama', 'Madya', 'Purwa'];
        $jenisList = ManajemenSkk::select('jenis_skk')->distinct()->pluck('jenis_skk');

        $pencapaianFinal = [];

        foreach ($siswas as $siswa) {
            foreach ($jenisList as $jenis) {
                $highestLulusTingkatanForJenis = null;

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
                        break;
                    }
                }

                if ($highestLulusTingkatanForJenis) {
                    $pencapaianFinal[] = $highestLulusTingkatanForJenis;
                }
            }
        }

        return view('pembina.lihat_pencapaian.pencapaian_skk', compact('pencapaianFinal'));
    }

    public function skkExport()
    {
        $pembina = Auth::user()->pembina;
        return Excel::download(new PencapaianSkkExport($pembina->kelas), 'pencapaian_skk.xlsx');
    }
}
