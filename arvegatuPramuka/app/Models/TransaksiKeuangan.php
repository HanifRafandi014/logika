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
}
