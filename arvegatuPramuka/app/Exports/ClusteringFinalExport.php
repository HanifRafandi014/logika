<?php

namespace App\Exports;

use App\Models\ClusteringFinal;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ClusteringFinalExport implements FromQuery, WithHeadings, WithMapping
{
    protected $gender;

    public function __construct($gender = null)
    {
        $this->gender = $gender;
    }

    public function query()
    {
        $query = ClusteringFinal::with('siswa');

        if ($this->gender !== null && in_array($this->gender, [0, 1])) {
            $query->whereHas('siswa', function ($q) {
                $q->where('jenis_kelamin', $this->gender);
            });
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'ID Siswa',
            'Nama Siswa',
            'Jenis Kelamin',
            'Kategori Lomba',
            'Rata-rata Skor',
        ];
    }

    public function map($item): array
    {
        $jenisKelaminText = '';
        if (isset($item->siswa->jenis_kelamin)) {
            $jenisKelaminText = $item->siswa->jenis_kelamin == 1 ? 'Laki-laki' : 'Perempuan';
        }

        return [
            $item->siswa_id,
            $item->siswa->nama ?? '-',
            $jenisKelaminText,
            $item->kategori_lomba,
            number_format($item->rata_rata_skor, 2),
        ];
    }
}
