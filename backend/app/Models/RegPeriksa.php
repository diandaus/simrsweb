<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegPeriksa extends Model
{
    protected $table = 'reg_periksa';
    protected $primaryKey = 'no_rawat';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'no_reg', 'no_rawat', 'tgl_registrasi', 'jam_reg',
        'kd_dokter', 'no_rkm_medis', 'kd_poli', 'p_jawab',
        'almt_pj', 'hubunganpj', 'biaya_reg', 'stts',
        'stts_daftar', 'status_lanjut', 'kd_pj', 'umurdaftar',
        'sttsumur', 'status_bayar', 'status_poli'
    ];

    protected $casts = [
        'tgl_registrasi' => 'date',
        'biaya_reg' => 'float'
    ];

    public function pasien()
    {
        return $this->belongsTo(Pasien::class, 'no_rkm_medis', 'no_rkm_medis');
    }

    public function dokter()
    {
        return $this->belongsTo(Pegawai::class, 'kd_dokter', 'nip');
    }
}
