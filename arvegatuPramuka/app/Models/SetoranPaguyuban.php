<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SetoranPaguyuban extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $casts = [
        'bulan_setor' => 'array', // Penting: Ini memberitahu Laravel untuk meng-cast ke array
    ];
    public function pengurus_kelas()
    {
        return $this->belongsTo(OrangTua::class, 'pengurus_kelas_id');
    }

    public function pengurus_besar()
    {
        return $this->belongsTo(OrangTua::class, 'pengurus_besar_id');
    }
    public function besaran_biaya()
    {
        return $this->belongsTo(BesaranBiaya::class, 'besaran_biaya_id');
    }

    public function transaksi_keuangan()
    {
        return $this->hasOne(TransaksiKeuangan::class, 'setoran_paguyuban_id');
    }
}
