<?php

namespace App\Imports;

use App\Models\Siswa;
use App\Models\NilaiNonAkademik;
use App\Models\User; // Pastikan ini mengarah ke model User Anda
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;
use Throwable;

class NilaiNonAkademikImport implements OnEachRow, WithHeadingRow
{
    protected $kategori;

    public function __construct($kategori)
    {
        $this->kategori = $kategori;
    }

    public function onRow(Row $row)
    {
        $r = $row->toArray();

        $siswa = Siswa::where('nisn', $r['nisn'])->first();
        // Memuat ulang user dengan relasi 'pembina'
        $userWithPembina = User::with('pembina')->find(Auth::id());

        // Pastikan user ada dan memiliki relasi 'pembina'
        if (!$siswa || !$userWithPembina || !$userWithPembina->pembina) {
            throw new \Exception("Data tidak valid pada baris: NISN '{$r['nisn']}' tidak ditemukan, atau pengguna tidak terautentikasi, atau pembina tidak ditemukan untuk pengguna ini.");
        }

        NilaiNonAkademik::create([
            'kategori'   => $this->kategori,
            'nilai'      => $r['nilai'],
            'siswa_id'   => $siswa->id,
            'pembina_id' => $userWithPembina->pembina->id,
        ]);
    }
}