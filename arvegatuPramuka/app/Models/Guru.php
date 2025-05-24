<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guru extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pembina()
    {
        return $this->hasOne(Pembina::class);
    }

    public function nilai_akademik()
    {
        return $this->hasMany(NilaiAkademik::class);
    }
}
