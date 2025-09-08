<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NilaiNonAkademik extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function pembina()
    {
        return $this->belongsTo(Pembina::class);
    }

    public function lomba()
    {
        return $this->belongsTo(Lomba::class);
    }
}
