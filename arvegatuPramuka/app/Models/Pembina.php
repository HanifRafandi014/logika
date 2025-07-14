<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembina extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function nilai_non_akademik()
    {
        return $this->hasMany(NilaiNonAkademik::class);
    }
    public function penilaian_skk()
    {
        return $this->hasMany(PenilaianSkk::class, 'pembina_id');
    }
    public function penilaian_sku()
    {
        return $this->hasMany(PenilaianSku::class, 'pembina_id');
    }

    public function siswa()
    {
        return $this->hasMany(Siswa::class);
    }
}
