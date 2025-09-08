<?php

namespace App\Imports;

use App\Models\Pembina;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;

class PembinaImport implements ToModel, WithHeadingRow, WithStartRow
{
    public function model(array $row)
    {
        $namaBersih = strtolower(str_replace(' ', '', $row['nama']));
        $generatedUsername = $namaBersih . '123';
        $generatedPassword = $generatedUsername;

        $existingUser = User::where('username', $generatedUsername)->first();

        if (!$existingUser) {
            $user = User::create([
                'username' => $generatedUsername,
                'password' => Hash::make($generatedPassword),
                'role' => 'pembina'
            ]);
        } else {
            // Cek apakah guru sudah terdaftar
            if (Pembina::where('user_id', $existingUser->id)->exists()) {
                return null; // Sudah ada guru dan user
            }
            $user = $existingUser;
        }

        return new Pembina([
            'nama' => $row['nama'],
            'kelas' => $row['kelas'],
            'nip' => $row['nip'],
            'kategori' => $row['kategori'],
            'status' => $row['status'],
            'user_id' => $user->id,
        ]);
    }

    public function startRow(): int
    {
        return 2; // Mulai pembacaan data dari baris kedua
    }
}
