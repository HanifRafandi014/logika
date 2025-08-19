<?php

namespace App\Exports;

use App\Models\Siswa;
use App\Models\PenilaianSku;
use App\Models\ManajemenSku;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PencapaianSkuExport implements FromCollection, WithHeadings
{
    protected $kelas;

    public function __construct($kelas)
    {
        $this->kelas = $kelas;
    }

    public function collection()
    {
        // Urutan tingkatan SKU dari tertinggi ke terendah
        $tingkatanOrder = ['Terap', 'Rakit', 'Ramu'];

        // Ambil siswa berdasarkan filter kelas
        $siswas = Siswa::where('kelas', $this->kelas)
            ->with('pembina')
            ->get();

        $dataToExport = [];

        foreach ($siswas as $siswa) {
            $siswaPencapaianSku = [];

            foreach ($tingkatanOrder as $tingkatan) {
                $penilaianItems = PenilaianSku::where('siswa_id', $siswa->id)
                    ->where('tingkatan', $tingkatan)
                    ->get();

                $jumlah_dinilai = $penilaianItems->where('status', 1)->count();
                $total_item = ManajemenSku::where('tingkatan', $tingkatan)->count();

                $statusLulus = ($jumlah_dinilai >= $total_item && $total_item > 0)
                    ? 'Lulus'
                    : 'Belum Lulus';

                if ($statusLulus === 'Lulus') {
                    $siswaPencapaianSku[$tingkatan] = [
                        'Nama Siswa' => $siswa->nama,
                        'Kelas' => $siswa->kelas,
                        'NISN' => $siswa->nisn,
                        'Tingkatan' => $tingkatan,
                        'Status' => $statusLulus,
                        'Tanggal' => $penilaianItems->max('tanggal') ?? '-',
                    ];
                }
            }

            // Ambil hanya tingkatan tertinggi yang lulus
            foreach ($tingkatanOrder as $tingkatan) {
                if (isset($siswaPencapaianSku[$tingkatan])) {
                    $dataToExport[] = $siswaPencapaianSku[$tingkatan];
                    break;
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
            'Tingkatan',
            'Status',
            'Tanggal',
        ];
    }
}
