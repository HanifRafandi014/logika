<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManajemenSku extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function penilaian_sku()
    {
        return $this->hasMany(PenilaianSku::class, 'manajemen_sku_id');
    }
}
