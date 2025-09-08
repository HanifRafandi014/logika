<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenilaianSku extends Model
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
    public function manajemen_sku()
    {
        return $this->belongsTo(ManajemenSku::class, 'manajemen_sku_id');
    }
}
