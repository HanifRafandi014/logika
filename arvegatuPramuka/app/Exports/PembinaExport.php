<?php

namespace App\Exports;

use App\Models\Pembina;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PembinaExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $pembinas = Pembina::all();
        return $pembinas;
    }

    public function headings(): array
    {
        return [
            'Nama',
            'NIP',
            'Kategori',
            'Status',
        ];
    }
}
