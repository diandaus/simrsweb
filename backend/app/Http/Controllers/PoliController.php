<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PoliController extends Controller
{
    /**
     * Get list of Poli (Rawat Jalan) patients
     */
    public function index(Request $request)
    {
        $tanggal_dari = $request->input('tanggal_dari', date('Y-m-d'));
        $tanggal_sampai = $request->input('tanggal_sampai', date('Y-m-d'));
        $search = $request->input('search', '');
        $kd_pj = $request->input('kd_pj', ''); // Penjab filter
        $nm_poli = $request->input('nm_poli', ''); // Poli filter
        $nm_dokter = $request->input('nm_dokter', ''); // Dokter filter
        $stts = $request->input('stts', ''); // Status filter (Belum/Sudah)
        $status_bayar = $request->input('status_bayar', ''); // Status Bayar filter

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
                'poliklinik.nm_poli',
                'reg_periksa.p_jawab',
                'reg_periksa.almt_pj',
                'reg_periksa.hubunganpj',
                'reg_periksa.biaya_reg',
                'reg_periksa.stts',
                'penjab.png_jawab',
                DB::raw("CONCAT(reg_periksa.umurdaftar, ' ', reg_periksa.sttsumur) as umur"),
                'reg_periksa.status_bayar',
                'reg_periksa.status_poli',
                'reg_periksa.kd_pj',
                'reg_periksa.kd_poli',
                'pasien.no_tlp'
            )
            ->whereBetween('reg_periksa.tgl_registrasi', [$tanggal_dari, $tanggal_sampai])
            ->where('reg_periksa.status_lanjut', 'Ralan'); // Only Rawat Jalan

        // Filter by Penjab
        if (!empty($kd_pj)) {
            $query->where('reg_periksa.kd_pj', 'like', "%{$kd_pj}%");
        }

        // Filter by Poli
        if (!empty($nm_poli)) {
            $query->where('poliklinik.nm_poli', 'like', "%{$nm_poli}%");
        }

        // Filter by Dokter
        if (!empty($nm_dokter)) {
            $query->where('dokter.nm_dokter', 'like', "%{$nm_dokter}%");
        }

        // Filter by Status
        if (!empty($stts) && $stts !== 'Semua') {
            $query->where('reg_periksa.stts', $stts);
        }

        // Filter by Status Bayar
        if (!empty($status_bayar) && $status_bayar !== 'Semua') {
            $query->where('reg_periksa.status_bayar', $status_bayar);
        }

        // Add search filter
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('reg_periksa.no_reg', 'like', "%{$search}%")
                  ->orWhere('reg_periksa.no_rawat', 'like', "%{$search}%")
                  ->orWhere('reg_periksa.tgl_registrasi', 'like', "%{$search}%")
                  ->orWhere('reg_periksa.kd_dokter', 'like', "%{$search}%")
                  ->orWhere('dokter.nm_dokter', 'like', "%{$search}%")
                  ->orWhere('reg_periksa.no_rkm_medis', 'like', "%{$search}%")
                  ->orWhere('pasien.nm_pasien', 'like', "%{$search}%")
                  ->orWhere('poliklinik.nm_poli', 'like', "%{$search}%")
                  ->orWhere('reg_periksa.p_jawab', 'like', "%{$search}%")
                  ->orWhere('penjab.png_jawab', 'like', "%{$search}%")
                  ->orWhere('reg_periksa.almt_pj', 'like', "%{$search}%")
                  ->orWhere('reg_periksa.status_bayar', 'like', "%{$search}%")
                  ->orWhere('reg_periksa.hubunganpj', 'like', "%{$search}%");
            });
        }

        $patients = $query->orderBy('reg_periksa.no_rawat', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $patients
        ]);
    }

    /**
     * Get list of Poli for filter dropdown
     */
    public function getPoli()
    {
        $poli = DB::table('poliklinik')
            ->select('kd_poli', 'nm_poli')
            ->where('status', '1')
            ->orderBy('nm_poli')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $poli
        ]);
    }

    /**
     * Get list of Dokter for filter dropdown
     */
    public function getDokter()
    {
        $dokter = DB::table('dokter')
            ->select('kd_dokter', 'nm_dokter')
            ->where('status', '1')
            ->orderBy('nm_dokter')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $dokter
        ]);
    }

    /**
     * Get list of Penjab for filter dropdown
     */
    public function getPenjab()
    {
        $penjab = DB::table('penjab')
            ->select('kd_pj', 'png_jawab')
            ->where('status', '1')
            ->orderBy('png_jawab')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $penjab
        ]);
    }

    /**
     * Get list of Dokter by Poliklinik
     */
    public function getDokterByPoli($kd_poli)
    {
        $dokter = DB::table('jadwal')
            ->join('dokter', 'jadwal.kd_dokter', '=', 'dokter.kd_dokter')
            ->select('dokter.kd_dokter', 'dokter.nm_dokter')
            ->where('jadwal.kd_poli', $kd_poli)
            ->where('dokter.status', '1')
            ->distinct()
            ->orderBy('dokter.nm_dokter')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $dokter
        ]);
    }

    /**
     * Store Rujukan Internal Poli
     */
    public function storeRujukanInternal(Request $request)
    {
        $request->validate([
            'no_rawat' => 'required',
            'kd_dokter' => 'required',
            'kd_poli' => 'required'
        ]);

        // Check if rujukan already exists
        $exists = DB::table('rujukan_internal_poli')
            ->where('no_rawat', $request->no_rawat)
            ->where('kd_dokter', $request->kd_dokter)
            ->where('kd_poli', $request->kd_poli)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Rujukan Sama'
            ], 400);
        }

        // Insert rujukan internal
        DB::table('rujukan_internal_poli')->insert([
            'no_rawat' => $request->no_rawat,
            'kd_dokter' => $request->kd_dokter,
            'kd_poli' => $request->kd_poli
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Rujukan internal berhasil disimpan'
        ]);
    }

    /**
     * Get list of Rujukan Internal Poli patients
     */
    public function rujukanInternal(Request $request)
    {
        $tanggal_dari = $request->input('tanggal_dari', date('Y-m-d'));
        $tanggal_sampai = $request->input('tanggal_sampai', date('Y-m-d'));
        $search = $request->input('search', '');
        $nm_poli = $request->input('nm_poli', '');
        $nm_dokter = $request->input('nm_dokter', '');
        $stts = $request->input('stts', '');

        $query = DB::table('reg_periksa')
            ->join('rujukan_internal_poli', 'rujukan_internal_poli.no_rawat', '=', 'reg_periksa.no_rawat')
            ->join('dokter', 'rujukan_internal_poli.kd_dokter', '=', 'dokter.kd_dokter')
            ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->join('poliklinik', 'rujukan_internal_poli.kd_poli', '=', 'poliklinik.kd_poli')
            ->join('penjab', 'reg_periksa.kd_pj', '=', 'penjab.kd_pj')
            ->select(
                'reg_periksa.no_rawat',
                'reg_periksa.tgl_registrasi',
                'reg_periksa.jam_reg',
                'rujukan_internal_poli.kd_dokter',
                'dokter.nm_dokter',
                'reg_periksa.no_rkm_medis',
                'pasien.nm_pasien',
                'poliklinik.nm_poli',
                'reg_periksa.p_jawab',
                'reg_periksa.almt_pj',
                'reg_periksa.hubunganpj',
                'reg_periksa.stts',
                'penjab.png_jawab',
                'rujukan_internal_poli.kd_poli',
                DB::raw("CONCAT(reg_periksa.umurdaftar, ' ', reg_periksa.sttsumur) as umur"),
                'reg_periksa.kd_pj',
                'pasien.no_tlp'
            )
            ->where('reg_periksa.status_lanjut', 'Ralan')
            ->whereBetween('reg_periksa.tgl_registrasi', [$tanggal_dari, $tanggal_sampai]);

        // Filter by Poli
        if (!empty($nm_poli)) {
            $query->where('poliklinik.nm_poli', 'like', "%{$nm_poli}%");
        }

        // Filter by Dokter
        if (!empty($nm_dokter)) {
            $query->where('dokter.nm_dokter', 'like', "%{$nm_dokter}%");
        }

        // Filter by Status
        if (!empty($stts) && $stts !== 'Semua') {
            $query->where('reg_periksa.stts', $stts);
        }

        // Add search filter
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('reg_periksa.no_reg', 'like', "%{$search}%")
                  ->orWhere('reg_periksa.no_rawat', 'like', "%{$search}%")
                  ->orWhere('reg_periksa.tgl_registrasi', 'like', "%{$search}%")
                  ->orWhere('rujukan_internal_poli.kd_dokter', 'like', "%{$search}%")
                  ->orWhere('dokter.nm_dokter', 'like', "%{$search}%")
                  ->orWhere('reg_periksa.no_rkm_medis', 'like', "%{$search}%")
                  ->orWhere('pasien.nm_pasien', 'like', "%{$search}%")
                  ->orWhere('poliklinik.nm_poli', 'like', "%{$search}%")
                  ->orWhere('reg_periksa.p_jawab', 'like', "%{$search}%")
                  ->orWhere('penjab.png_jawab', 'like', "%{$search}%")
                  ->orWhere('reg_periksa.almt_pj', 'like', "%{$search}%")
                  ->orWhere('reg_periksa.hubunganpj', 'like', "%{$search}%");
            });
        }

        $patients = $query->orderBy('reg_periksa.no_rawat', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $patients
        ]);
    }
}
