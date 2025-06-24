<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManajemenSkk extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function penilaian_skk()
    {
        return $this->hasMany(PenilaianSkk::class, 'manajemen_skk_id');
    }
}
