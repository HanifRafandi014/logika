<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BesaranBiaya extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function pembayaran_spp()
    {
        return $this->hasMany(PembayaranSpp::class);
    }
    public function setoran_paguyuban()
    {
        return $this->hasMany(SetoranPaguyuban::class, 'besaran_biaya_id');
    }
}
