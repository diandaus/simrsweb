<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class UserWeb extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'user_web';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'username',
        'password',
        'nama',
        'nip',
        'email',
        'no_telp',
        'role',
        'status',
        'menu_permissions',
        'foto_profil'
    ];

    protected $hidden = [
        'password'
    ];

    protected $casts = [
        'menu_permissions' => 'array'
    ];

    public function getAuthIdentifierName()
    {
        return 'username';
    }

    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * Relationship dengan table pegawai
     * NIP di user_web match dengan NIK di pegawai
     */
    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'nip', 'nik');
    }

    /**
     * Relationship dengan table dokter
     * NIP di user_web match dengan kd_dokter di dokter
     */
    public function dokter()
    {
        return $this->belongsTo(\App\Models\Dokter::class, 'nip', 'kd_dokter');
    }

    /**
     * Relationship dengan table petugas
     * NIP di user_web match dengan nip di petugas
     */
    public function petugas()
    {
        return $this->belongsTo(\App\Models\Petugas::class, 'nip', 'nip');
    }
}
