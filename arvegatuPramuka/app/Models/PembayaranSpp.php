<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranSpp extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $appends = ['total_bayar_final'];
    public function getTotalBayarFinalAttribute(): float
    {
        $jumlahBulan = 0;
        // Pastikan bulan_bayar adalah string JSON dan bisa di-decode
        if ($this->bulan_bayar && is_string($this->bulan_bayar)) {
            $decodedBulanBayar = json_decode($this->bulan_bayar, true);
            if (is_array($decodedBulanBayar)) {
                $jumlahBulan = count($decodedBulanBayar);
            }
        }
        $biayaPerBulan = $this->besaran_biaya?->total_biaya ?? 0;
        return $biayaPerBulan * $jumlahBulan;
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function orang_tua()
    {
        return $this->belongsTo(OrangTua::class);
    }
    public function besaran_biaya()
    {
        return $this->belongsTo(BesaranBiaya::class);
    }
}
