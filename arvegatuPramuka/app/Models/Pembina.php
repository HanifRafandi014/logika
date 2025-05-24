<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembina extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    public function nilai_non_akademik()
    {
        return $this->hasMany(NilaiNonAkademik::class);
    }
}
