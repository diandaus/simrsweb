<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    protected $table = 'pegawai';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'nik', 'nama', 'jk', 'tmp_lahir', 'tgl_lahir',
        'alamat', 'email', 'departemen', 'jbtn',
        'pendidikan', 'gapok', 'stts_wp', 'stts_kerja'
    ];

    protected $hidden = [];
}