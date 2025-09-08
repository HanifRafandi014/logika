<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VariabelClustering extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $casts = [
        'variabel_akademiks' => 'array',
        'variabel_non_akademiks' => 'array',
    ];

    public function lombas()
    {
        return $this->hasMany(Lomba::class, 'variabel_clustering_id');
    }
}
