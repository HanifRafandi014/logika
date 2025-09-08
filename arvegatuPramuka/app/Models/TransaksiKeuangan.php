<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiKeuangan extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function pengurus_besar()
    {
        return $this->belongsTo(OrangTua::class, 'pengurus_besar_id');
    }

    public function setoran_paguyuban()
    {
        return $this->belongsTo(SetoranPaguyuban::class, 'setoran_paguyuban_id');
    }
}
