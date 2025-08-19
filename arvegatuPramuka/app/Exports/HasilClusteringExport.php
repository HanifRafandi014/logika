<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Models\HasilClustering;

class HasilClusteringExport implements FromCollection, WithHeadings, WithMapping
{
    protected $no = 0; // Inisialisasi nomor urut

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        // Ambil data hasil clustering beserta relasi siswa
        return HasilClustering::with('siswa')->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'No',
            'Nama Siswa',
            'Kategori Lomba',
            'Nilai Rata-rata'
        ];
    }

    /**
     * @param mixed $row
     *
     * @return array
     */
    public function map($row): array
    {
        $this->no++;

        return [
            $this->no,
            optional($row->siswa)->nama ?? '-', // pakai optional() biar aman kalau null
            $row->kategori_lomba,
            $row->rata_rata_skor,
        ];
    }
}
