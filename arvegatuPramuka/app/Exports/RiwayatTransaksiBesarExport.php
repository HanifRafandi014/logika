<?php

namespace App\Exports;

use App\Models\SetoranPaguyuban;
use App\Models\TransaksiKeuangan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RiwayatTransaksiBesarExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        // This logic is similar to riwayatTransaksiBesar in the controller
        $totalPemasukan = SetoranPaguyuban::sum('jumlah');

        $pengeluaranBulanan = TransaksiKeuangan::selectRaw('MONTH(tanggal_transaksi) as bulan, SUM(jumlah) as total_pengeluaran')
            ->where('jenis_transaksi', 'pengeluaran')
            ->groupByRaw('MONTH(tanggal_transaksi)')
            ->pluck('total_pengeluaran', 'bulan')
            ->toArray();

        $bulanList = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $riwayat = [];
        $saldoBerjalan = $totalPemasukan;

        $minMonthWithTransaction = TransaksiKeuangan::min(DB::raw('MONTH(tanggal_transaksi)'));
        $maxMonthWithTransaction = TransaksiKeuangan::max(DB::raw('MONTH(tanggal_transaksi)'));

        if (is_null($minMonthWithTransaction) || is_null($maxMonthWithTransaction)) {
            foreach ($bulanList as $num => $nama) {
                $riwayat[] = [
                    'bulan' => $nama,
                    'saldo_awal' => '-',
                    'pengeluaran' => '-',
                    'saldo_akhir' => '-'
                ];
            }
        } else {
            foreach ($bulanList as $num => $nama) {
                $pengeluaran = $pengeluaranBulanan[$num] ?? 0;

                if ($num >= $minMonthWithTransaction && $num <= $maxMonthWithTransaction) {
                    $saldoAwal = $saldoBerjalan;
                    $saldoAkhir = $saldoAwal - $pengeluaran;

                    $riwayat[] = [
                        'bulan' => $nama,
                        'saldo_awal' => $saldoAwal,
                        'pengeluaran' => $pengeluaran,
                        'saldo_akhir' => $saldoAkhir
                    ];
                    $saldoBerjalan = $saldoAkhir;
                } else {
                    $riwayat[] = [
                        'bulan' => $nama,
                        'saldo_awal' => '-',
                        'pengeluaran' => '-',
                        'saldo_akhir' => '-'
                    ];
                }
            }
        }

        return collect($riwayat);
    }

    public function headings(): array
    {
        return [
            'Bulan',
            'Saldo Awal (Rp)',
            'Pengeluaran (Rp)',
            'Saldo Akhir (Rp)',
        ];
    }

    public function map($row): array
    {
        return [
            $row['bulan'],
            is_numeric($row['saldo_awal']) ? $row['saldo_awal'] : '-',
            is_numeric($row['pengeluaran']) ? $row['pengeluaran'] : '-',
            is_numeric($row['saldo_akhir']) ? $row['saldo_akhir'] : '-',
        ];
    }
}