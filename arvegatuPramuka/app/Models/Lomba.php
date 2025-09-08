<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lomba extends Model
{
    use HasFactory;
    protected $guarded = [];

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

    public function variabel()
    {
        return $this->belongsTo(VariabelClustering::class, 'variabel_clustering_id');
    }
}
