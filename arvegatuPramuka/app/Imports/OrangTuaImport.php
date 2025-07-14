<?php

namespace App\Imports;

use App\Models\OrangTua;
use App\Models\User;
use App\Models\Siswa; // Import the Siswa model
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation; // For adding validation rules

class OrangTuaImport implements ToModel, WithHeadingRow, WithStartRow, WithValidation
{
    public function model(array $row)
    {
        // Generate username and password for the user
        $namaBersih = strtolower(str_replace(' ', '', $row['nama']));
        $generatedUsername = $namaBersih . '123';
        $generatedPassword = $generatedUsername;

        // Find or create the user. If found, it won't create a new one.
        $user = User::firstOrCreate(
            ['username' => $generatedUsername],
            [
                'password' => Hash::make($generatedPassword),
                'role' => 'orang_tua'
            ]
        );

        // Check if an OrangTua record already exists for this user.
        // This prevents creating multiple parent records if importing the same user repeatedly.
        if (OrangTua::where('user_id', $user->id)->exists()) {
            return null; // If OrangTua already exists, skip this row
        }

        // --- Core Logic for siswa_id mapping ---
        $siswaId = null;
        if (isset($row['nama_siswa']) && !empty($row['nama_siswa'])) {
            // Trim whitespace and ensure case-insensitivity if needed,
            // but for 'exists' validation, exact match is usually required by default.
            $siswa = Siswa::where('nama', $row['nama_siswa'])->first();
            if ($siswa) {
                $siswaId = $siswa->id;
            }
        }
        // --- End Core Logic ---

        // Return a new OrangTua model instance
        return new OrangTua([
            'nama' => $row['nama'],
            'no_hp' => $row['no_hp'],
            'alamat' => $row['alamat'],
            'status' => $row['status'],
            'siswa_id' => $siswaId, // Assign the found siswa_id (or null if not found)
            'user_id' => $user->id,
        ]);
    }

    public function startRow(): int
    {
        return 2; // Start reading data from the second row (skipping headers)
    }

    public function rules(): array
    {
        return [
            'nama' => 'required|string|max:255',
            'no_hp' => 'required|string|max:20',
            'alamat' => 'required|string|max:255',
            'status' => 'required|in:Anggota,Pengurus Paguyuban Kelas,Pengurus Paguyuban Besar',
            'nama_siswa' => 'nullable|string|exists:siswas,nama',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'nama_siswa.exists' => 'Nama siswa ":input" tidak ditemukan dalam database. Pastikan nama siswa di Excel sudah benar dan terdaftar.',
        ];
    }
}