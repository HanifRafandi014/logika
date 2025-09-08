<?php

namespace App\Imports;

use App\Models\Siswa;
use App\Models\NilaiAkademik;
use App\Models\User; // Pastikan ini mengarah ke model User Anda
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;
use Throwable;

class NilaiAkademikImport implements OnEachRow, WithHeadingRow
{
    protected $mata_pelajaran;

    public function __construct($mata_pelajaran)
    {
        $this->mata_pelajaran = $mata_pelajaran;
    }

    public function onRow(Row $row)
    {
        $r = $row->toArray();

        $siswa = Siswa::where('nisn', $r['nisn'])->first();
        $userWithGuru = User::with('guru')->find(Auth::id());

        // Pastikan user ada dan memiliki relasi 'pembina'
        if (!$siswa || !$userWithGuru || !$userWithGuru->guru) {
            throw new \Exception("Data tidak valid pada baris: NISN '{$r['nisn']}' tidak ditemukan, atau pengguna tidak terautentikasi, atau guru tidak ditemukan untuk pengguna ini.");
        }

        NilaiAkademik::create([
            'mata_pelajaran'   => $this->mata_pelajaran,
            'nilai'      => $r['nilai'],
            'siswa_id'   => $siswa->id,
            'guru_id' => $userWithGuru->guru->id,
        ]);
    }
}