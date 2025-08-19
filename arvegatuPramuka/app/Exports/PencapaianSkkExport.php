<?php

namespace App\Exports;

use App\Models\Siswa;
use App\Models\PenilaianSkk;
use App\Models\ManajemenSkk;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PencapaianSkkExport implements FromCollection, WithHeadings
{
    protected $kelas;

    public function __construct($kelas)
    {
        $this->kelas = $kelas;
    }

    public function collection()
    {
        // Urutan tingkatan SKK dari tertinggi ke terendah
        $tingkatanOrder = ['Utama', 'Madya', 'Purwa'];

        // Ambil semua jenis SKK unik
        $jenisList = ManajemenSkk::select('jenis_skk')->distinct()->pluck('jenis_skk');

        // Ambil siswa sesuai kelas yang dipilih
        $siswas = Siswa::where('kelas', $this->kelas)
            ->with('pembina')
            ->get();

        $dataToExport = [];

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

                    $statusLulus = ($jumlah_dinilai >= $total_item && $total_item > 0)
                        ? 'Lulus'
                        : 'Belum Lulus';

                    if ($statusLulus === 'Lulus') {
                        $highestLulusTingkatanForJenis = [
                            'Nama Siswa' => $siswa->nama,
                            'Kelas' => $siswa->kelas,
                            'NISN' => $siswa->nisn,
                            'Jenis SKK' => $jenis,
                            'Tingkatan' => $tingkatan,
                            'Status' => $statusLulus,
                            'Tanggal' => $penilaianItems->max('tanggal') ?? '-',
                        ];
                        break; // Sudah ketemu tingkatan tertinggi untuk jenis SKK ini
                    }
                }

                if ($highestLulusTingkatanForJenis) {
                    $dataToExport[] = $highestLulusTingkatanForJenis;
                }
            }
        }

        return collect($dataToExport);
    }

    public function headings(): array
    {
        return [
            'Nama Siswa',
            'Kelas',
            'NISN',
            'Jenis SKK',
            'Tingkatan',
            'Status',
            'Tanggal',
        ];
    }
}
