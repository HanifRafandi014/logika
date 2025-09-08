<?php

namespace App\Exports;

use App\Models\SetoranPaguyuban;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class RekapanSetoranExport implements FromCollection, WithHeadings, WithMapping
{
    protected $pengurusKelasId;

    public function __construct(int $pengurusKelasId)
    {
        $this->pengurusKelasId = $pengurusKelasId;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return SetoranPaguyuban::where('pengurus_kelas_id', $this->pengurusKelasId)
            ->orderBy('bulan_setor', 'desc') // Assuming bulan_setor is a sortable field or using created_at
            ->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'No',
            'Bulan Iuran',
            'Total Pembayaran (Rp)',
            'Jumlah Dibayarkan (Rp)',
            'Tanggal Bayar',
            'Status Verifikasi',
        ];
    }

    /**
     * @var \App\Models\SetoranPaguyuban $setoran
     */
    public function map($setoran): array
    {
        static $rowNumber = 0;
        $rowNumber++;

        // Convert bulan_setor (which is an array of month names) to a readable string
        $bulanIuran = implode(', ', $setoran->bulan_setor ?? []);

        return [
            $rowNumber,
            $bulanIuran,
            number_format($setoran->total, 0, ',', '.'),
            number_format($setoran->jumlah, 0, ',', '.'),
            Carbon::parse($setoran->created_at)->translatedFormat('j F Y'),
            $setoran->status_verifikasi ? 'Sudah diverifikasi' : 'Belum Diverifikasi',
        ];
    }
}