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
        // Mengambil data yang diperlukan dari model HasilClustering
        return HasilClustering::select('nama_siswa', 'kategori_lomba', 'rata_rata_skor')->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        // Mendefinisikan header untuk kolom-kolom di Excel
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
        $this->no++; // Increment nomor urut untuk setiap baris
        // Memetakan data dari setiap baris ke format yang diinginkan untuk ekspor
        return [
            $this->no,
            $row->nama_siswa,
            $row->kategori_lomba,
            $row->rata_rata_skor,
        ];
    }
}

