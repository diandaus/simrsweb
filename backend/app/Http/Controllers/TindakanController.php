<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TindakanController extends Controller
{
    /**
     * Get list of jenis perawatan for tindakan
     */
    public function getJenisPerawatan(Request $request)
    {
        $search = $request->input('search', '');

        $query = DB::table('jns_perawatan')
            ->select(
                'kd_jenis_prw',
                'nm_perawatan',
                'kd_kategori',
                'total_byrdr',
                'kd_pj',
                'kd_poli'
            )
            ->where('status', '1');

        // Add search filter
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('kd_jenis_prw', 'like', "%{$search}%")
                  ->orWhere('nm_perawatan', 'like', "%{$search}%");
            });
        }

        $jenisPerawatan = $query->orderBy('nm_perawatan')->get();

        return response()->json([
            'success' => true,
            'data' => $jenisPerawatan
        ]);
    }

    /**
     * Store tindakan rawat jalan dokter
     */
    public function storeTindakan(Request $request)
    {
        $request->validate([
            'no_rawat' => 'required',
            'tindakan' => 'required|array',
            'tindakan.*.kd_jenis_prw' => 'required',
            'tindakan.*.nm_perawatan' => 'required',
            'tindakan.*.biaya_rawat' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $noRawat = $request->no_rawat;

            // Get reg_periksa data to get kd_dokter
            $regPeriksa = DB::table('reg_periksa')
                ->where('no_rawat', $noRawat)
                ->first();

            if (!$regPeriksa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data registrasi tidak ditemukan'
                ], 404);
            }

            $tglPerawatan = date('Y-m-d');
            $jamRawat = date('H:i:s');

            // Get jenis perawatan details for calculating components
            foreach ($request->tindakan as $item) {
                $jenisPerawatan = DB::table('jns_perawatan')
                    ->where('kd_jenis_prw', $item['kd_jenis_prw'])
                    ->first();

                if (!$jenisPerawatan) {
                    continue;
                }

                // Insert rawat_jl_dr
                DB::table('rawat_jl_dr')->insert([
                    'no_rawat' => $noRawat,
                    'kd_jenis_prw' => $item['kd_jenis_prw'],
                    'kd_dokter' => $regPeriksa->kd_dokter,
                    'tgl_perawatan' => $tglPerawatan,
                    'jam_rawat' => $jamRawat,
                    'biaya_rawat' => $jenisPerawatan->total_byrdr ?? $item['biaya_rawat'],
                    'tarif_tindakandr' => $jenisPerawatan->tarif_tindakandr ?? 0,
                    'kso' => $jenisPerawatan->kso ?? 0,
                    'menejemen' => $jenisPerawatan->menejemen ?? 0,
                    'material' => $jenisPerawatan->material ?? 0,
                    'bhp' => $jenisPerawatan->bhp ?? 0,
                    'stts_bayar' => 'Belum'
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tindakan berhasil disimpan'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan tindakan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get riwayat tindakan by no_rawat
     */
    public function getRiwayat($no_rawat)
    {
        try {
            // Get riwayat tindakan - hanya hari ini
            $riwayat = DB::table('rawat_jl_dr')
                ->join('jns_perawatan', 'rawat_jl_dr.kd_jenis_prw', '=', 'jns_perawatan.kd_jenis_prw')
                ->join('dokter', 'rawat_jl_dr.kd_dokter', '=', 'dokter.kd_dokter')
                ->select(
                    'rawat_jl_dr.no_rawat',
                    'rawat_jl_dr.tgl_perawatan',
                    'rawat_jl_dr.jam_rawat',
                    'rawat_jl_dr.kd_jenis_prw',
                    'jns_perawatan.nm_perawatan',
                    'rawat_jl_dr.biaya_rawat',
                    'rawat_jl_dr.kd_dokter',
                    'dokter.nm_dokter'
                )
                ->where('rawat_jl_dr.no_rawat', $no_rawat)
                ->whereDate('rawat_jl_dr.tgl_perawatan', date('Y-m-d'))
                ->orderBy('rawat_jl_dr.tgl_perawatan', 'desc')
                ->orderBy('rawat_jl_dr.jam_rawat', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $riwayat
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat riwayat tindakan: ' . $e->getMessage()
            ], 500);
        }
    }
}
