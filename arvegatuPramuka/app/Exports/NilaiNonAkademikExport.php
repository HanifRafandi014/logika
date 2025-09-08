<?php

namespace App\Exports;

use App\Models\Siswa;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class NilaiNonAkademikExport implements FromCollection, WithHeadings
{
    // Daftar kategori nilai non-akademik
    private $categories = [
        'Nilai Tes Bahasa',
        'Nilai TIK',
        'Kehadiran',
        'Skor Penerapan',
        'Nilai Hasta Karya',
    ];

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        // Mengambil semua siswa beserta nilai non-akademik mereka
        // Memuat relasi 'nilai_non_akademik' dan memfilter berdasarkan kategori yang ditentukan
        $siswas = Siswa::with(['nilai_non_akademik' => function($query) {
            $query->whereIn('kategori', $this->categories);
        }])->get();

        // Memproses data siswa untuk format yang sesuai dengan tabel
        $data = $siswas->map(function ($siswa, $index) {
            $row = [
                'no' => $index + 1, // Nomor urut
                'nisn' => $siswa->nisn, // Kolom NISN dari model Siswa
                'nama_siswa' => $siswa->nama, // Kolom Nama Siswa dari model Siswa
            ];

            // Mengelompokkan nilai non-akademik siswa berdasarkan kategori
            $scoresByCategory = $siswa->nilai_non_akademik->keyBy('kategori');

            // Menambahkan nilai untuk setiap kategori non-akademik
            foreach ($this->categories as $category) {
                $score = $scoresByCategory->get($category);
                // Menggunakan nama kolom yang sesuai dengan format DataTables (misal: nilai_tes_bahasa, nilai_tik)
                // Ini penting agar header dan data di Excel cocok
                $columnName = strtolower(str_replace(' ', '_', $category));
                $row[$columnName] = $score ? $score->nilai : '-'; // Jika tidak ada nilai, tampilkan '-'
            }
            return $row;
        });

        return new Collection($data);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        // Mendefinisikan header kolom untuk file Excel
        $headers = [
            'No',
            'NISN',
            'Nama Siswa',
        ];

        // Menambahkan header untuk setiap kategori non-akademik
        foreach ($this->categories as $category) {
            $headers[] = $category;
        }

        return $headers;
    }
}
