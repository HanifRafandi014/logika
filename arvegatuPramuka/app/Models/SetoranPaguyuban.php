<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SetoranPaguyuban extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function pengurus_kelas()
    {
        return $this->belongsTo(OrangTua::class, 'pengurus_kelas_id');
    }

    public function pengurus_besar()
    {
        return $this->belongsTo(OrangTua::class, 'pengurus_besar_id');
    }
}
