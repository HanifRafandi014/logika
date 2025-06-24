<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrangTua extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function pembayaran_spp()
    {
        return $this->hasMany(PembayaranSpp::class);
    }
    public function setoran_dibuat()
    {
        return $this->hasMany(SetoranPaguyuban::class, 'pengurus_kelas_id');
    }

    public function setoran_diterima()
    {
        return $this->hasMany(SetoranPaguyuban::class, 'pengurus_besar_id');
    }

    public function transaksi_keuangan()
    {
        return $this->hasMany(TransaksiKeuangan::class, 'pengurus_besar_id');
    }
}
