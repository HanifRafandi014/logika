<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function admin(){
        return $this->hasOne(Admin::class);
    }
    public function alumni(){
        return $this->hasOne(Alumni::class);
    }
    public function guru(){
        return $this->hasOne(Guru::class);
    }
    public function pembina(){
        return $this->hasOne(Pembina::class);
    }
    public function siswa(){
        return $this->hasOne(related: Siswa::class);
    }
    public function orang_tua(){
        return $this->hasOne(related: OrangTua::class);
    }
}
