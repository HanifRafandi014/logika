<?php

namespace App\Exports;

use App\Models\Pembina;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PembinaExport
{
    public function export($kategori = null, $status = null)
    {
        $query = Pembina::query();
        dd($query);

        if ($kategori) {
            $query->where('kategori', $kategori);
        }

        if ($status !== null && $status !== '') {
            $statusBoolean = null;
            if ($status === 'Pembina PA') $statusBoolean = 1;
            elseif ($status === 'Pembina PI') $statusBoolean = 0;

            if ($statusBoolean !== null) {
                $query->where('status', $statusBoolean);
            }
        }

        $data = $query->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Judul
        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A1', 'DATA PEMBINA TAHUN 2025');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'd8e4bc']
            ]
        ]);

        // Header
        $headers = ['No', 'Nama', 'Kelas', 'NIP', 'Kategori', 'Status'];
        $startRow = 3;
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $startRow, $header);
            $sheet->getStyle($col . $startRow)->applyFromArray([
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'c5d9f1']
                ],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                ],
            ]);
            $sheet->getColumnDimension($col)->setWidth(20);
            $col++;
        }

        // Isi data
        $row = $startRow + 1;
        $no = 1;

        foreach ($data as $pembina) {
            $sheet->fromArray([
                $no++,
                $pembina->nama ?? 'N/A',
                $pembina->kelas ?? 'N/A',
                $pembina->nip ?? 'N/A',
                $pembina->kategori ?? 'N/A',
                ($pembina->status == 1) ? 'Pembina PA' : 'Pembina PI',
            ], null, 'A' . $row);

            $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
                'alignment' => ['wrapText' => true, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                ],
            ]);

            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'Data_Pembina_' . date('d-m-Y') . '.xlsx';
        $tempFile = storage_path('app/public/' . $filename);
        $writer->save($tempFile);

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}
