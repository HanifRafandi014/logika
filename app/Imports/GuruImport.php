<?php

namespace App\Imports;

use App\Models\Guru;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;

class GuruImport implements ToModel, WithHeadingRow, WithStartRow
{
    public function model(array $row)
    {
        // Generate username dari nama atau nip
        $namaBersih = strtolower(str_replace(' ', '', $row['nama']));
        $generatedUsername = $namaBersih . '123';
        $generatedPassword = $generatedUsername;

        $existingUser = User::where('username', $generatedUsername)->first();

        if (!$existingUser) {
            $user = User::create([
                'username' => $generatedUsername,
                'password' => Hash::make($generatedPassword),
                'role' => 'guru'
            ]);
        } else {
            // Cek apakah guru sudah terdaftar
            if (Guru::where('user_id', $existingUser->id)->exists()) {
                return null; // Sudah ada guru dan user
            }
            $user = $existingUser;
        }

        return new Guru([
            'nama' => $row['nama'],
            'mata_pelajaran' => $row['mata_pelajaran'],
            'nip' => $row['nip'],
            'pembina_pramuka' => $row['pembina_pramuka'],
            'user_id' => $user->id,
        ]);
    }

    public function startRow(): int
    {
        return 2; // Mulai pembacaan data dari baris kedua
    }
}
