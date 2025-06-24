<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;
    protected $table = 'event_alumni';
    protected $guarded = [];
    public function alumni()
    {
        return $this->belongsTo(Alumni::class);
    }
}
