<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IGDController extends Controller
{
    /**
     * Get list of IGD patients
     */
    public function index(Request $request)
    {
        $tanggal_dari = $request->input('tanggal_dari', date('Y-m-d'));
        $tanggal_sampai = $request->input('tanggal_sampai', date('Y-m-d'));
        $search = $request->input('search', '');

        $query = DB::table('reg_periksa')
            ->join('dokter', 'reg_periksa.kd_dokter', '=', 'dokter.kd_dokter')
            ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->join('poliklinik', 'reg_periksa.kd_poli', '=', 'poliklinik.kd_poli')
            ->join('penjab', 'reg_periksa.kd_pj', '=', 'penjab.kd_pj')
            ->select(
                'reg_periksa.no_reg',
                'reg_periksa.no_rawat',
                'reg_periksa.tgl_registrasi',
                'reg_periksa.jam_reg',
                'reg_periksa.kd_dokter',
                'dokter.nm_dokter',
                'reg_periksa.no_rkm_medis',
                'pasien.nm_pasien',
                'pasien.jk',
                DB::raw("CONCAT(reg_periksa.umurdaftar, ' ', reg_periksa.sttsumur) as umur"),
                'poliklinik.nm_poli',
                'reg_periksa.p_jawab',
                'reg_periksa.almt_pj',
                'reg_periksa.hubunganpj',
                'reg_periksa.biaya_reg',
                'reg_periksa.stts_daftar',
                'penjab.png_jawab',
                'reg_periksa.stts',
                'reg_periksa.kd_pj',
                'reg_periksa.status_bayar'
            )
            ->where('poliklinik.kd_poli', 'IGDK')
            ->whereBetween('reg_periksa.tgl_registrasi', [$tanggal_dari, $tanggal_sampai]);

        // Add search filter
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('reg_periksa.no_reg', 'like', "%{$search}%")
                  ->orWhere('reg_periksa.no_rawat', 'like', "%{$search}%")
                  ->orWhere('reg_periksa.tgl_registrasi', 'like', "%{$search}%")
                  ->orWhere('reg_periksa.kd_dokter', 'like', "%{$search}%")
                  ->orWhere('dokter.nm_dokter', 'like', "%{$search}%")
                  ->orWhere('reg_periksa.no_rkm_medis', 'like', "%{$search}%")
                  ->orWhere('reg_periksa.stts_daftar', 'like', "%{$search}%")
                  ->orWhere('pasien.nm_pasien', 'like', "%{$search}%")
                  ->orWhere('poliklinik.nm_poli', 'like', "%{$search}%")
                  ->orWhere('reg_periksa.p_jawab', 'like', "%{$search}%")
                  ->orWhere('reg_periksa.almt_pj', 'like', "%{$search}%")
                  ->orWhere('reg_periksa.hubunganpj', 'like', "%{$search}%")
                  ->orWhere('penjab.png_jawab', 'like', "%{$search}%");
            });
        }

        $patients = $query->orderBy('reg_periksa.no_rawat', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $patients
        ]);
    }
}
