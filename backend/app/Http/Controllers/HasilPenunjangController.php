<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HasilPenunjangController extends Controller
{
    /**
     * Store hasil pemeriksaan penunjang (EKG/USG)
     */
    public function store(Request $request)
    {
        $request->validate([
            'no_rawat' => 'required',
            'jenis_pemeriksaan' => 'required|in:EKG,USG',
            'hasil' => 'required',
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

            $tglPeriksa = date('Y-m-d');
            $jamPeriksa = date('H:i:s');

            // Insert hasil_penunjang_penunjang
            DB::table('hasil_penunjang_penunjang')->insert([
                'no_rawat' => $noRawat,
                'tgl_periksa' => $tglPeriksa,
                'jam_periksa' => $jamPeriksa,
                'jenis_pemeriksaan' => $request->jenis_pemeriksaan,
                'hasil' => $request->hasil,
                'kd_dokter' => $regPeriksa->kd_dokter
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Hasil pemeriksaan berhasil disimpan'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan hasil pemeriksaan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get riwayat hasil pemeriksaan penunjang by no_rawat
     */
    public function getRiwayat($no_rawat)
    {
        try {
            // Get riwayat hasil penunjang - hanya hari ini
            $riwayat = DB::table('hasil_penunjang_penunjang')
                ->join('dokter', 'hasil_penunjang_penunjang.kd_dokter', '=', 'dokter.kd_dokter')
                ->select(
                    'hasil_penunjang_penunjang.no_rawat',
                    'hasil_penunjang_penunjang.tgl_periksa',
                    'hasil_penunjang_penunjang.jam_periksa',
                    'hasil_penunjang_penunjang.jenis_pemeriksaan',
                    'hasil_penunjang_penunjang.hasil',
                    'hasil_penunjang_penunjang.kd_dokter',
                    'dokter.nm_dokter'
                )
                ->where('hasil_penunjang_penunjang.no_rawat', $no_rawat)
                ->whereDate('hasil_penunjang_penunjang.tgl_periksa', date('Y-m-d'))
                ->orderBy('hasil_penunjang_penunjang.tgl_periksa', 'desc')
                ->orderBy('hasil_penunjang_penunjang.jam_periksa', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $riwayat
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat riwayat hasil pemeriksaan: ' . $e->getMessage()
            ], 500);
        }
    }
}
