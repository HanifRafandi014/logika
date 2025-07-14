<?php

namespace App\Exports;

use App\Models\SetoranPaguyuban;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class RekapIuranPramukaExport implements FromCollection, WithHeadings, WithMapping
{
    protected $bulan;

    public function __construct(string $bulan = null)
    {
        $this->bulan = $bulan;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = SetoranPaguyuban::with('pengurus_kelas.siswa');

        if ($this->bulan) {
            // Map the month name to a numeric month
            $bulan_numerik = array_search($this->bulan, [
                'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli',
                'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ]) + 1;

            if ($bulan_numerik) {
                // Filter the data by month using a whereMonth clause
                $query->whereMonth('bulan_setor', $bulan_numerik);
            }
        }

        return $query->orderBy('bulan_setor', 'desc')->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'No',
            'Kelas Asal',
            'Bulan Iuran Disetor',
            'Pengurus Kelas',
            'Siswa Pengurus',
            'Jumlah Setoran',
            'Tanggal Setor',
        ];
    }

    /**
     * @var SetoranPaguyuban $setoran
     */
    public function map($setoran): array
    {
        static $rowNumber = 0;
        $rowNumber++;

        return [
            $rowNumber,
            $setoran->kelas,
            Carbon::parse($setoran->bulan_setor)->translatedFormat('F Y'),
            $setoran->pengurus_kelas->nama ?? 'N/A',
            $setoran->pengurus_kelas->siswa->nama ?? 'N/A',
            $setoran->jumlah,
            Carbon::parse($setoran->bulan_setor)->translatedFormat('d F Y'),
        ];
    }
}