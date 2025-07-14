<?php

namespace App\Exports;

use App\Models\Siswa;
use App\Models\PenilaianSku;
use App\Models\ManajemenSku;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PencapaianSkuExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        // Define the order of skill levels from highest to lowest
        $tingkatanOrder = ['Terap', 'Rakit', 'Ramu'];

        // Get all students with their pembina (mentor) relationship
        $siswas = Siswa::with('pembina')->get();

        // Initialize an array to store the final filtered data for export
        $dataToExport = [];

        // Iterate through each student
        foreach ($siswas as $siswa) {
            // Initialize an array to store the passed SKU achievements for the current student
            $siswaPencapaianSku = [];

            // Iterate through each skill level in descending order of priority
            foreach ($tingkatanOrder as $tingkatan) {
                // Get all PenilaianSku items for the current student and skill level
                $penilaianItems = PenilaianSku::where('siswa_id', $siswa->id)
                    ->where('tingkatan', $tingkatan)
                    ->get();

                // Count the number of items marked as 'status = 1' (assumed to be 'passed')
                $jumlah_dinilai = $penilaianItems->where('status', 1)->count();

                // Get the total number of SKU items for this skill level
                $total_item = ManajemenSku::where('tingkatan', $tingkatan)->count();

                // Determine the pass/fail status for the current skill level
                // A level is 'Lulus' (passed) if all required items are assessed and passed, and there are items to assess.
                $statusLulus = ($jumlah_dinilai >= $total_item && $total_item > 0) ? 'Lulus' : 'Belum Lulus';

                // If the student has passed this skill level, store its details
                if ($statusLulus === 'Lulus') {
                    $siswaPencapaianSku[$tingkatan] = [
                        'Nama Siswa' => $siswa->nama,
                        'Kelas' => $siswa->kelas,
                        'NISN' => $siswa->nisn,
                        'Tingkatan' => $tingkatan,
                        'Status' => $statusLulus,
                        // Get the latest date of assessment for this level, or '-' if none
                        'Tanggal' => $penilaianItems->max('tanggal') ?? '-',
                    ];
                }
            }

            // After checking all skill levels for the current student, find the highest level passed
            $highestLulusTingkatan = null;
            foreach ($tingkatanOrder as $tingkatan) {
                if (isset($siswaPencapaianSku[$tingkatan])) {
                    $highestLulusTingkatan = $siswaPencapaianSku[$tingkatan];
                    break; // Found the highest passed level, exit the loop
                }
            }

            // If a highest passed skill level is found for the student, add it to the export data
            if ($highestLulusTingkatan) {
                $dataToExport[] = $highestLulusTingkatan;
            }
        }

        // Return the collected data as a Laravel collection
        return collect($dataToExport);
    }

    public function headings(): array
    {
        // Define the headings for the Excel file
        return [
            'Nama Siswa',
            'Kelas',
            'NISN',
            'Tingkatan',
            'Status',
            'Tanggal',
        ];
    }
}
