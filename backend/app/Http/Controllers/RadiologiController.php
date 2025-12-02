<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RadiologiController extends Controller
{
    /**
     * Get list of pemeriksaan radiologi
     */
    public function getPemeriksaan(Request $request)
    {
        $search = $request->input('search', '');

        $query = DB::table('jns_perawatan_radiologi')
            ->select(
                'kd_jenis_prw',
                'nm_perawatan',
                'kd_pj',
                'kelas',
                'total_byr'
            )
            ->where('status', '1');

        // Add search filter
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('kd_jenis_prw', 'like', "%{$search}%")
                  ->orWhere('nm_perawatan', 'like', "%{$search}%");
            });
        }

        $pemeriksaan = $query->orderBy('kd_jenis_prw')->get();

        return response()->json([
            'success' => true,
            'data' => $pemeriksaan
        ]);
    }

    /**
     * Store permintaan radiologi
     */
    public function storePermintaan(Request $request)
    {
        $request->validate([
            'no_rawat' => 'required',
            'pemeriksaan' => 'required|array',
            'pemeriksaan.*.kd_jenis_prw' => 'required',
            'pemeriksaan.*.nm_perawatan' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $noRawat = $request->no_rawat;
            $diagnosisKlinis = $request->diagnosa_klinis ?? '';
            $informasiTambahan = $request->informasi_tambahan ?? '';

            // Get reg_periksa data
            $regPeriksa = DB::table('reg_periksa')
                ->where('no_rawat', $noRawat)
                ->first();

            if (!$regPeriksa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data registrasi tidak ditemukan'
                ], 404);
            }

            // Determine status based on status_lanjut
            $status = $regPeriksa->status_lanjut == 'Ranap' ? 'ranap' : 'ralan';

            // Generate noorder
            $tglPermintaan = date('Y-m-d');
            $jamPermintaan = date('H:i:s');
            $noorder = $this->generateNoOrder($tglPermintaan);

            // Insert permintaan_radiologi (header)
            $inserted = DB::table('permintaan_radiologi')->insert([
                'noorder' => $noorder,
                'no_rawat' => $noRawat,
                'tgl_permintaan' => $tglPermintaan,
                'jam_permintaan' => $jamPermintaan,
                'tgl_sampel' => '0000-00-00',
                'jam_sampel' => '00:00:00',
                'tgl_hasil' => '0000-00-00',
                'jam_hasil' => '00:00:00',
                'dokter_perujuk' => $regPeriksa->kd_dokter,
                'status' => $status,
                'informasi_tambahan' => $informasiTambahan,
                'diagnosa_klinis' => $diagnosisKlinis
            ]);

            if (!$inserted) {
                // Retry dengan auto nomor baru
                $noorder = $this->generateNoOrder($tglPermintaan);
                $inserted = DB::table('permintaan_radiologi')->insert([
                    'noorder' => $noorder,
                    'no_rawat' => $noRawat,
                    'tgl_permintaan' => $tglPermintaan,
                    'jam_permintaan' => $jamPermintaan,
                    'tgl_sampel' => '0000-00-00',
                    'jam_sampel' => '00:00:00',
                    'tgl_hasil' => '0000-00-00',
                    'jam_hasil' => '00:00:00',
                    'dokter_perujuk' => $regPeriksa->kd_dokter,
                    'status' => $status,
                    'informasi_tambahan' => $informasiTambahan,
                    'diagnosa_klinis' => $diagnosisKlinis
                ]);
            }

            // Insert permintaan_pemeriksaan_radiologi (detail)
            foreach ($request->pemeriksaan as $item) {
                DB::table('permintaan_pemeriksaan_radiologi')->insert([
                    'noorder' => $noorder,
                    'kd_jenis_prw' => $item['kd_jenis_prw'],
                    'stts_bayar' => 'Belum'
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Permintaan radiologi berhasil disimpan',
                'noorder' => $noorder
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan permintaan radiologi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate noorder untuk permintaan radiologi
     */
    private function generateNoOrder($tglPermintaan)
    {
        // Get last noorder for today
        $lastOrder = DB::table('permintaan_radiologi')
            ->whereDate('tgl_permintaan', $tglPermintaan)
            ->orderBy('noorder', 'desc')
            ->first();

        if ($lastOrder) {
            // Extract nomor urut dari noorder
            $lastNumber = intval(substr($lastOrder->noorder, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return date('Ymd') . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get riwayat permintaan radiologi by no_rawat
     */
    public function getRiwayat($no_rawat)
    {
        try {
            // Get all permintaan grouped by noorder - hanya hari ini
            $permintaanList = DB::table('permintaan_radiologi')
                ->join('dokter', 'permintaan_radiologi.dokter_perujuk', '=', 'dokter.kd_dokter')
                ->join('reg_periksa', 'permintaan_radiologi.no_rawat', '=', 'reg_periksa.no_rawat')
                ->leftJoin('penjab', 'reg_periksa.kd_pj', '=', 'penjab.kd_pj')
                ->select(
                    'permintaan_radiologi.noorder',
                    'permintaan_radiologi.no_rawat',
                    'permintaan_radiologi.tgl_permintaan',
                    'permintaan_radiologi.jam_permintaan',
                    'permintaan_radiologi.tgl_sampel',
                    'permintaan_radiologi.jam_sampel',
                    'permintaan_radiologi.tgl_hasil',
                    'permintaan_radiologi.jam_hasil',
                    'permintaan_radiologi.informasi_tambahan',
                    'permintaan_radiologi.diagnosa_klinis',
                    DB::raw('dokter.nm_dokter as nm_dokter_perujuk'),
                    'penjab.png_jawab'
                )
                ->where('permintaan_radiologi.no_rawat', $no_rawat)
                ->whereDate('permintaan_radiologi.tgl_permintaan', date('Y-m-d'))
                ->groupBy(
                    'permintaan_radiologi.noorder',
                    'permintaan_radiologi.no_rawat',
                    'permintaan_radiologi.tgl_permintaan',
                    'permintaan_radiologi.jam_permintaan',
                    'permintaan_radiologi.tgl_sampel',
                    'permintaan_radiologi.jam_sampel',
                    'permintaan_radiologi.tgl_hasil',
                    'permintaan_radiologi.jam_hasil',
                    'permintaan_radiologi.informasi_tambahan',
                    'permintaan_radiologi.diagnosa_klinis',
                    'dokter.nm_dokter',
                    'penjab.png_jawab'
                )
                ->orderBy('permintaan_radiologi.tgl_permintaan', 'desc')
                ->orderBy('permintaan_radiologi.jam_permintaan', 'desc')
                ->get();

            $results = [];
            foreach ($permintaanList as $permintaan) {
                // Get detail pemeriksaan for this noorder
                $details = DB::table('permintaan_pemeriksaan_radiologi')
                    ->join('jns_perawatan_radiologi', 'permintaan_pemeriksaan_radiologi.kd_jenis_prw', '=', 'jns_perawatan_radiologi.kd_jenis_prw')
                    ->select(
                        'jns_perawatan_radiologi.kd_jenis_prw',
                        'jns_perawatan_radiologi.nm_perawatan',
                        'permintaan_pemeriksaan_radiologi.stts_bayar'
                    )
                    ->where('permintaan_pemeriksaan_radiologi.noorder', $permintaan->noorder)
                    ->get();

                $results[] = [
                    'noorder' => $permintaan->noorder,
                    'no_rawat' => $permintaan->no_rawat,
                    'tgl_permintaan' => $permintaan->tgl_permintaan,
                    'jam_permintaan' => $permintaan->jam_permintaan,
                    'tgl_sampel' => $permintaan->tgl_sampel != '0000-00-00' ? $permintaan->tgl_sampel : null,
                    'jam_sampel' => $permintaan->jam_sampel != '00:00:00' ? $permintaan->jam_sampel : null,
                    'tgl_hasil' => $permintaan->tgl_hasil != '0000-00-00' ? $permintaan->tgl_hasil : null,
                    'jam_hasil' => $permintaan->jam_hasil != '00:00:00' ? $permintaan->jam_hasil : null,
                    'informasi_tambahan' => $permintaan->informasi_tambahan,
                    'diagnosa_klinis' => $permintaan->diagnosa_klinis,
                    'dokter_perujuk' => $permintaan->nm_dokter_perujuk,
                    'png_jawab' => $permintaan->png_jawab,
                    'pemeriksaan' => $details
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $results
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat riwayat radiologi: ' . $e->getMessage()
            ], 500);
        }
    }
}
