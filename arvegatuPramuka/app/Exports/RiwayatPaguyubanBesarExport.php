<?php

namespace App\Exports;

use App\Models\TransaksiKeuangan; // Make sure to import the TransaksiKeuangan model
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth; // To get the logged-in user's ID
use Illuminate\Support\Facades\Storage; // Add this line to import the Storage facade

class RiwayatPaguyubanBesarExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Get the logged-in Pengurus Besar's ID
        $pengurusBesarId = Auth::user()->orang_tua->id;

        // Fetch transactions related to this Pengurus Besar
        return TransaksiKeuangan::with('setoran_paguyuban')
                                ->where('pengurus_besar_id', $pengurusBesarId)
                                ->orderBy('tanggal_transaksi', 'desc')
                                ->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'No',
            'Jenis Transaksi',
            'Kategori',
            'Jumlah (Rp)',
            'Tanggal Transaksi',
            'Bulan Bayar',
            'URL Bukti Transaksi',
        ];
    }

    /**
     * @var \App\Models\TransaksiKeuangan $transaksi
     */
    public function map($transaksi): array
    {
        static $rowNumber = 0;
        $rowNumber++;

        // Determine 'Bulan Bayar / Setor' based on transaction type
        $bulanBayarSetor = '-';
        if ($transaksi->jenis_transaksi === 'pemasukan' && $transaksi->setoran_paguyuban) {
            // Assuming bulan_setor is a JSON array of month names in SetoranPaguyuban
            $bulanBayarSetor = implode(', ', $transaksi->setoran_paguyuban->bulan_setor ?? []);
        } elseif ($transaksi->jenis_transaksi === 'pengeluaran') {
            // For expenditures, use the transaction month
            $bulanBayarSetor = Carbon::parse($transaksi->tanggal_transaksi)->translatedFormat('F');
        }

        // Generate full URL for the proof, if available
        // Use Storage::url() to get the public URL for the stored file
        $buktiUrl = $transaksi->bukti_transaksi ? url(Storage::url($transaksi->bukti_transaksi)) : '-';

        return [
            $rowNumber,
            $transaksi->jenis_transaksi === 'pemasukan' ? 'Pemasukan' : 'Pengeluaran',
            $transaksi->kategori ?? '-',
            number_format($transaksi->jumlah, 0, ',', '.'),
            Carbon::parse($transaksi->tanggal_transaksi)->translatedFormat('d F Y'),
            $bulanBayarSetor,
            $buktiUrl,
        ];
    }
}
