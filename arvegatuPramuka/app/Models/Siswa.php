<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function alumni()
    {
        return $this->hasOne(Alumni::class);
    }

    public function nilai_akademik()
    {
        return $this->hasMany(NilaiAkademik::class);
    }

    public function nilai_non_akademik()
    {
        return $this->hasMany(NilaiNonAkademik::class);
    }

    public function orang_tua()
    {
        return $this->hasMany(OrangTua::class);
    }

    public function pembayaran_spp()
    {
        return $this->hasMany(PembayaranSpp::class);
    }
}
