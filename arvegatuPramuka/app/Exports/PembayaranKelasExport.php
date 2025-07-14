<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class PembayaranKelasExport implements FromCollection, WithHeadings, WithMapping
{
    protected $dataRekapan;

    public function __construct(array $dataRekapan)
    {
        $this->dataRekapan = $dataRekapan;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $exportData = collect();

        foreach ($this->dataRekapan as $rekapan) {
            if ($rekapan['riwayat_pembayaran']->isEmpty()) {
                $exportData->push([
                    'No' => '', // Will be filled by row number in map()
                    'Nama Siswa' => $rekapan['siswa_nama'],
                    'Nama Orang Tua' => $rekapan['orang_tua_nama'],
                    'Bulan Iuran' => 'Belum ada pembayaran',
                    'Jumlah' => '',
                    'Tanggal Bayar' => '',
                    'Status' => '',
                ]);
            } else {
                foreach ($rekapan['riwayat_pembayaran'] as $pembayaran) {
                    $exportData->push([
                        'No' => '', // Will be filled by row number in map()
                        'Nama Siswa' => $rekapan['siswa_nama'],
                        'Nama Orang Tua' => $rekapan['orang_tua_nama'],
                        'Bulan Iuran' => Carbon::parse($pembayaran->created_at)->translatedFormat('F Y'),
                        'Jumlah' => $pembayaran->besaran_biaya->total_biaya ?? 0, // Ensure default if besaran_biaya is null
                        'Tanggal Bayar' => Carbon::parse($pembayaran->created_at)->translatedFormat('j F Y'),
                        'Status' => $pembayaran->status_pembayaran ? 'Lunas' : 'Belum Lunas',
                    ]);
                }
            }
        }

        return $exportData;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'No',
            'Nama Siswa',
            'Nama Orang Tua',
            'Bulan Iuran',
            'Jumlah (Rp)',
            'Tanggal Bayar',
            'Status',
        ];
    }

    /**
     * @var mixed $row
     *
     * @return array
     */
    public function map($row): array
    {
        static $i = 0; // Static counter for row numbering
        $i++;

        return [
            $i, // Auto-incrementing row number
            $row['Nama Siswa'],
            $row['Nama Orang Tua'],
            $row['Bulan Iuran'],
            $row['Jumlah'] ? number_format($row['Jumlah'], 0, ',', '.') : '', // Format number
            $row['Tanggal Bayar'],
            $row['Status'],
        ];
    }
}