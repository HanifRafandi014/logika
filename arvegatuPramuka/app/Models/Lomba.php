<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lomba extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $casts = [
        'nilai_akademiks'    => 'array',
        'nilai_non_akademiks' => 'array',
        'status'                => 'boolean', // Assuming 'status' is a boolean
    ];
    public function getRelatedNilaiAkademiksAttribute()
    {
        $ids = $this->nilai_akademiks ?? [];
        return NilaiAkademik::whereIn('id', $ids)->get();
    }

    public function getRelatedNilaiNonAkademiksAttribute()
    {
        $ids = $this->nilai_non_akademiks ?? [];
        return NilaiNonAkademik::whereIn('id', $ids)->get();
    }
}
