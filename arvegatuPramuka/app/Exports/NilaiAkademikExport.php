<?php

namespace App\Exports;

use App\Models\Siswa;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class NilaiAkademikExport implements FromCollection, WithHeadings
{
    // Daftar kategori mata pelajaran
    private $categories = [
        'Matematika',
        'IPA',
        'IPS',
        'Olahraga',
        'Bahasa Indonesia',
        'Bahasa Inggris',
    ];

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        // Mengambil semua siswa beserta nilai akademik mereka
        // Memuat relasi 'nilai_akademik' dan memfilter berdasarkan kategori yang ditentukan
        $siswas = Siswa::with(['nilai_akademik' => function($query) {
            $query->whereIn('mata_pelajaran', $this->categories);
        }])->get();

        // Memproses data siswa untuk format yang sesuai dengan tabel
        $data = $siswas->map(function ($siswa, $index) {
            $row = [
                'no' => $index + 1, // Nomor urut
                'nisn' => $siswa->nisn, // Kolom NISN dari model Siswa
                'nama_siswa' => $siswa->nama, // Kolom Nama Siswa dari model Siswa
            ];

            // Mengelompokkan nilai akademik siswa berdasarkan mata pelajaran
            $scoresBySubject = $siswa->nilai_akademik->keyBy('mata_pelajaran');

            // Menambahkan nilai untuk setiap kategori mata pelajaran
            foreach ($this->categories as $category) {
                $score = $scoresBySubject->get($category);
                // Menggunakan nama kolom yang sesuai dengan format DataTables (misal: matematika, ipa)
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

        // Menambahkan header untuk setiap kategori mata pelajaran
        foreach ($this->categories as $category) {
            $headers[] = $category;
        }

        return $headers;
    }
}
