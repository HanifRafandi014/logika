<?php

namespace App\Imports;

use App\Models\Siswa;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;

class SiswaImport implements ToModel, WithHeadingRow, WithStartRow
{
    public function model(array $row)
    {
        // Lewati baris kosong
        if (empty($row['nama']) || empty($row['nisn'])) {
            return null;
        }

        $namaBersih = strtolower(str_replace(' ', '', $row['nama']));
        $generatedUsername = $namaBersih . '123';
        $generatedPassword = $generatedUsername;

        $existingUser = User::where('username', $generatedUsername)->first();

        if (!$existingUser) {
            $user = User::create([
                'username' => $generatedUsername,
                'password' => Hash::make($generatedPassword),
                'role' => 'siswa'
            ]);
        } else {
            if (Siswa::where('user_id', $existingUser->id)->exists()) {
                return null;
            }
            $user = $existingUser;
        }

        return new Siswa([
            'nama' => $row['nama'],
            'kelas' => $row['kelas'],
            'nisn' => $row['nisn'],
            'jenis_kelamin' => $row['jenis_kelamin'],
            'angkatan' => $row['angkatan'],
            'user_id' => $user->id,
        ]);
    }

    public function startRow(): int
    {
        return 2; // Mulai pembacaan data dari baris kedua
    }
}
