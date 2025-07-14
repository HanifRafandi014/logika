<?php

namespace App\Exports;

use App\Models\Siswa;
use App\Models\PenilaianSkk;
use App\Models\ManajemenSkk;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PencapaianSkkExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        // Define the order of SKK skill levels from highest to lowest
        $tingkatanOrder = ['Utama', 'Madya', 'Purwa'];
        // Get all unique SKK types from the ManajemenSkk table
        $jenisList = ManajemenSkk::select('jenis_skk')->distinct()->pluck('jenis_skk');
        // Get all students with their pembina (mentor) relationship
        $siswas = Siswa::with('pembina')->get();

        // Initialize an array to store the final filtered data for export
        $dataToExport = [];

        // Iterate through each student
        foreach ($siswas as $siswa) {
            // Iterate through each unique SKK type
            foreach ($jenisList as $jenis) {
                $highestLulusTingkatanForJenis = null; // To store the highest passed level for this SKK type

                // Iterate through each skill level in descending order of priority
                foreach ($tingkatanOrder as $tingkatan) {
                    // Get all PenilaianSkk items for the current student, skill level, and SKK type
                    $penilaianItems = PenilaianSkk::where('siswa_id', $siswa->id)
                        ->where('tingkatan', $tingkatan)
                        ->where('jenis_skk', $jenis)
                        ->get();

                    // Count the number of items marked as 'status = 1' (assumed to be 'passed')
                    $jumlah_dinilai = $penilaianItems->where('status', 1)->count();

                    // Get the total number of SKK items for this skill level and SKK type
                    $total_item = ManajemenSkk::where('tingkatan', $tingkatan)
                        ->where('jenis_skk', $jenis)
                        ->count();

                    // Determine the pass/fail status for the current skill level
                    $statusLulus = ($jumlah_dinilai >= $total_item && $total_item > 0) ? 'Lulus' : 'Belum Lulus';

                    // If the student has passed this skill level for this SKK type, store its details
                    if ($statusLulus === 'Lulus') {
                        $highestLulusTingkatanForJenis = [
                            'Nama Siswa' => $siswa->nama,
                            'Kelas' => $siswa->kelas, // Include Kelas
                            'NISN' => $siswa->nisn,
                            'Jenis SKK' => $jenis,
                            'Tingkatan' => $tingkatan,
                            'Status' => $statusLulus,
                            // Get the latest date of assessment for this level, or '-' if none
                            'Tanggal' => $penilaianItems->max('tanggal') ?? '-',
                        ];
                        break; // Exit the tingkatan loop as we found the highest passed level for this SKK type
                    }
                }
                // If a highest passed skill level is found for this SKK type, add it to the export data
                if ($highestLulusTingkatanForJenis) {
                    $dataToExport[] = $highestLulusTingkatanForJenis;
                }
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
            'Jenis SKK',
            'Tingkatan',
            'Status',
            'Tanggal',
        ];
    }
}
