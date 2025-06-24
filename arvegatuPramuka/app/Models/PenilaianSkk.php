<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenilaianSkk extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function pembina()
    {
        return $this->belongsTo(Pembina::class, 'pembina_id');
    }
    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }
    public function manajemen_skk()
    {
        return $this->belongsTo(ManajemenSkk::class, 'manajemen_skk_id');
    }
}
