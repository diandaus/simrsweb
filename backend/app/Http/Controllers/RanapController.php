<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RanapController extends Controller
{
    /**
     * Get list of pasien rawat inap (belum pulang / sudah pulang)
     */
    public function index(Request $request)
    {
        try {
            $filterMode = $request->query('filter_mode', 'belum_pulang'); // belum_pulang, tgl_masuk, tgl_keluar
            $bangsal = $request->query('bangsal', '');
            $statusBayar = $request->query('status_bayar', '');
            $tglMasukStart = $request->query('tgl_masuk_start');
            $tglMasukEnd = $request->query('tgl_masuk_end');
            $tglKeluarStart = $request->query('tgl_keluar_start');
            $tglKeluarEnd = $request->query('tgl_keluar_end');
            $search = $request->query('search', '');

            // Base query
            $query = DB::table('kamar_inap')
                ->join('reg_periksa', 'kamar_inap.no_rawat', '=', 'reg_periksa.no_rawat')
                ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
                ->join('kamar', 'kamar_inap.kd_kamar', '=', 'kamar.kd_kamar')
                ->join('bangsal', 'kamar.kd_bangsal', '=', 'bangsal.kd_bangsal')
                ->join('kelurahan', 'pasien.kd_kel', '=', 'kelurahan.kd_kel')
                ->join('kecamatan', 'pasien.kd_kec', '=', 'kecamatan.kd_kec')
                ->join('kabupaten', 'pasien.kd_kab', '=', 'kabupaten.kd_kab')
                ->join('dokter', 'reg_periksa.kd_dokter', '=', 'dokter.kd_dokter')
                ->join('penjab', 'reg_periksa.kd_pj', '=', 'penjab.kd_pj')
                ->select(
                    'kamar_inap.no_rawat',
                    'reg_periksa.no_rkm_medis',
                    'pasien.nm_pasien',
                    DB::raw("CONCAT(pasien.alamat, ', ', kelurahan.nm_kel, ', ', kecamatan.nm_kec, ', ', kabupaten.nm_kab) as alamat"),
                    'reg_periksa.p_jawab',
                    'reg_periksa.hubunganpj',
                    'penjab.png_jawab',
                    DB::raw("CONCAT(kamar_inap.kd_kamar, ' ', bangsal.nm_bangsal) as kamar"),
                    'kamar_inap.trf_kamar',
                    'kamar_inap.diagnosa_awal',
                    'kamar_inap.diagnosa_akhir',
                    'kamar_inap.tgl_masuk',
                    'kamar_inap.jam_masuk',
                    DB::raw("IF(kamar_inap.tgl_keluar='0000-00-00', '', kamar_inap.tgl_keluar) as tgl_keluar"),
                    DB::raw("IF(kamar_inap.jam_keluar='00:00:00', '', kamar_inap.jam_keluar) as jam_keluar"),
                    'kamar_inap.ttl_biaya',
                    'kamar_inap.stts_pulang',
                    'kamar_inap.lama',
                    'dokter.nm_dokter',
                    'kamar_inap.kd_kamar',
                    'reg_periksa.kd_pj',
                    DB::raw("CONCAT(reg_periksa.umurdaftar, ' ', reg_periksa.sttsumur) as umur"),
                    'reg_periksa.status_bayar',
                    'pasien.agama',
                    'bangsal.nm_bangsal'
                );

            // Apply filter mode
            if ($filterMode === 'belum_pulang') {
                $query->where('kamar_inap.stts_pulang', '-');
            } elseif ($filterMode === 'tgl_masuk' && $tglMasukStart && $tglMasukEnd) {
                $query->whereBetween('kamar_inap.tgl_masuk', [$tglMasukStart, $tglMasukEnd]);
            } elseif ($filterMode === 'tgl_keluar' && $tglKeluarStart && $tglKeluarEnd) {
                $query->whereBetween('kamar_inap.tgl_keluar', [$tglKeluarStart, $tglKeluarEnd]);
            }

            // Apply bangsal filter
            if (!empty($bangsal)) {
                $query->where('bangsal.nm_bangsal', $bangsal);
            }

            // Apply status bayar filter
            if (!empty($statusBayar)) {
                $query->where('reg_periksa.status_bayar', 'like', '%' . $statusBayar . '%');
            }

            // Apply search filter
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('kamar_inap.no_rawat', 'like', '%' . $search . '%')
                      ->orWhere('reg_periksa.no_rkm_medis', 'like', '%' . $search . '%')
                      ->orWhere('pasien.nm_pasien', 'like', '%' . $search . '%')
                      ->orWhereRaw("CONCAT(pasien.alamat, ', ', kelurahan.nm_kel, ', ', kecamatan.nm_kec, ', ', kabupaten.nm_kab) like ?", ['%' . $search . '%'])
                      ->orWhere('kamar_inap.kd_kamar', 'like', '%' . $search . '%')
                      ->orWhere('bangsal.nm_bangsal', 'like', '%' . $search . '%')
                      ->orWhere('kamar_inap.diagnosa_awal', 'like', '%' . $search . '%')
                      ->orWhere('kamar_inap.diagnosa_akhir', 'like', '%' . $search . '%')
                      ->orWhere('kamar_inap.tgl_masuk', 'like', '%' . $search . '%')
                      ->orWhere('dokter.nm_dokter', 'like', '%' . $search . '%')
                      ->orWhere('kamar_inap.stts_pulang', 'like', '%' . $search . '%')
                      ->orWhere('kamar_inap.tgl_keluar', 'like', '%' . $search . '%')
                      ->orWhere('penjab.png_jawab', 'like', '%' . $search . '%')
                      ->orWhere('pasien.agama', 'like', '%' . $search . '%');
                });
            }

            // Order by
            $query->orderBy('kamar_inap.tgl_masuk', 'desc')
                  ->orderBy('kamar_inap.jam_masuk', 'desc');

            // Execute query
            $data = $query->get();

            // Get ranap gabung (bayi) for each record
            foreach ($data as $item) {
                $ranapGabung = DB::table('ranap_gabung')
                    ->join('reg_periksa', 'ranap_gabung.no_rawat2', '=', 'reg_periksa.no_rawat')
                    ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
                    ->select(
                        'pasien.no_rkm_medis',
                        'pasien.nm_pasien',
                        'ranap_gabung.no_rawat2',
                        DB::raw("CONCAT(reg_periksa.umurdaftar, ' ', reg_periksa.sttsumur) as umur"),
                        'pasien.no_peserta',
                        DB::raw("CONCAT(pasien.alamatpj, ', ', pasien.kelurahanpj, ', ', pasien.kecamatanpj, ', ', pasien.kabupatenpj) as alamat")
                    )
                    ->where('ranap_gabung.no_rawat', $item->no_rawat)
                    ->first();

                $item->ranap_gabung = $ranapGabung;
            }

            return response()->json([
                'success' => true,
                'data' => $data,
                'count' => $data->count()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data rawat inap: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get list of bangsal for filter
     */
    public function getBangsal()
    {
        try {
            $bangsal = DB::table('bangsal')
                ->select('kd_bangsal', 'nm_bangsal')
                ->where('status', '1')
                ->orderBy('nm_bangsal', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $bangsal
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data bangsal: ' . $e->getMessage()
            ], 500);
        }
    }
}
