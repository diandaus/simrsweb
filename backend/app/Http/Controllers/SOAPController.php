<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SOAPController extends Controller
{
    /**
     * Store SOAP data
     */
    public function store(Request $request)
    {
        $request->validate([
            'no_rawat' => 'required',
            'subjective' => 'required',
            'objective' => 'required',
            'assessment' => 'required',
            'planning' => 'nullable'
        ]);

        try {
            // Get user data - nip dari user_web
            $user = auth()->user();
            $nip = $user ? $user->nip : null;

            if (!$nip) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak memiliki NIP. Pastikan user terhubung dengan pegawai/dokter/petugas.'
                ], 401);
            }

            // Insert into pemeriksaan_ralan table
            DB::table('pemeriksaan_ralan')->insert([
                'no_rawat' => $request->no_rawat,
                'tgl_perawatan' => date('Y-m-d'),
                'jam_rawat' => date('H:i:s'),
                'suhu_tubuh' => $request->suhu ?? '',
                'tensi' => $request->tensi ?? '',
                'nadi' => $request->nadi ?? '',
                'respirasi' => $request->respirasi ?? '',
                'tinggi' => $request->tinggi ?? '',
                'berat' => $request->berat ?? '',
                'spo2' => '',
                'gcs' => '',
                'kesadaran' => 'Compos Mentis',
                'keluhan' => $request->subjective,
                'pemeriksaan' => $request->objective,
                'alergi' => '',
                'lingkar_perut' => '',
                'rtl' => $request->planning ?? '',
                'penilaian' => $request->assessment,
                'instruksi' => $request->instruksi ?? '',
                'evaluasi' => $request->evaluasi ?? '',
                'nip' => $nip
            ]);

            // Update status reg_periksa menjadi "Sudah"
            DB::table('reg_periksa')
                ->where('no_rawat', $request->no_rawat)
                ->update(['stts' => 'Sudah']);

            return response()->json([
                'success' => true,
                'message' => 'SOAP berhasil disimpan'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan SOAP: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get SOAP history for a patient
     */
    public function history($no_rawat)
    {
        try {
            $history = DB::table('pemeriksaan_ralan')
                ->leftJoin('reg_periksa', 'pemeriksaan_ralan.no_rawat', '=', 'reg_periksa.no_rawat')
                ->leftJoin('poliklinik', 'reg_periksa.kd_poli', '=', 'poliklinik.kd_poli')
                ->select(
                    'pemeriksaan_ralan.*',
                    'poliklinik.nm_poli as nm_unit'
                )
                ->where('pemeriksaan_ralan.no_rawat', $no_rawat)
                ->orderBy('pemeriksaan_ralan.tgl_perawatan', 'desc')
                ->orderBy('pemeriksaan_ralan.jam_rawat', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $history
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil riwayat SOAP: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update SOAP data
     */
    public function update(Request $request)
    {
        // Get parameters from query string
        $no_rawat = $request->query('no_rawat');
        $tgl_perawatan = $request->query('tgl_perawatan');
        $jam_rawat = $request->query('jam_rawat');

        // Debug log
        \Log::info('SOAP Update called', [
            'no_rawat' => $no_rawat,
            'tgl_perawatan' => $tgl_perawatan,
            'jam_rawat' => $jam_rawat
        ]);

        $request->validate([
            'subjective' => 'required',
            'objective' => 'required',
            'assessment' => 'required',
            'planning' => 'nullable'
        ]);

        try {
            // Get user data - nip dari user_web
            $user = auth()->user();
            $nip = $user ? $user->nip : null;

            if (!$nip) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak memiliki NIP. Pastikan user terhubung dengan pegawai/dokter/petugas.'
                ], 401);
            }

            // Update pemeriksaan_ralan table
            $updated = DB::table('pemeriksaan_ralan')
                ->where('no_rawat', $no_rawat)
                ->where('tgl_perawatan', $tgl_perawatan)
                ->where('jam_rawat', $jam_rawat)
                ->update([
                    'suhu_tubuh' => $request->suhu ?? '',
                    'tensi' => $request->tensi ?? '',
                    'nadi' => $request->nadi ?? '',
                    'respirasi' => $request->respirasi ?? '',
                    'tinggi' => $request->tinggi ?? '',
                    'berat' => $request->berat ?? '',
                    'keluhan' => $request->subjective,
                    'pemeriksaan' => $request->objective,
                    'rtl' => $request->planning ?? '',
                    'penilaian' => $request->assessment,
                    'instruksi' => $request->instruksi ?? '',
                    'evaluasi' => $request->evaluasi ?? '',
                    'nip' => $nip
                ]);

            if ($updated) {
                return response()->json([
                    'success' => true,
                    'message' => 'SOAP berhasil diupdate'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Data SOAP tidak ditemukan atau tidak ada perubahan'
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate SOAP: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete SOAP data
     */
    public function destroy($no_rawat, $tgl_perawatan, $jam_rawat)
    {
        try {
            $deleted = DB::table('pemeriksaan_ralan')
                ->where('no_rawat', $no_rawat)
                ->where('tgl_perawatan', $tgl_perawatan)
                ->where('jam_rawat', $jam_rawat)
                ->delete();

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'SOAP berhasil dihapus'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Data SOAP tidak ditemukan'
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus SOAP: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Riwayat SOAPIE for a patient (all registrations with SOAP data)
     */
    public function riwayatSOAPIE($no_rkm_medis)
    {
        try {
            // Get all registrations for the patient (limit 5 latest)
            $registrations = DB::table('reg_periksa')
                ->where('no_rkm_medis', $no_rkm_medis)
                ->where('stts', '<>', 'Batal')
                ->orderBy('tgl_registrasi', 'desc')
                ->limit(5)
                ->get();

            $result = [];

            foreach ($registrations as $reg) {
                // Get pemeriksaan_ralan data
                $pemeriksaanRalan = DB::table('pemeriksaan_ralan')
                    ->leftJoin('pegawai', 'pemeriksaan_ralan.nip', '=', 'pegawai.nik')
                    ->select(
                        'pemeriksaan_ralan.*',
                        'pegawai.nama',
                        'pegawai.jbtn'
                    )
                    ->where('pemeriksaan_ralan.no_rawat', $reg->no_rawat)
                    ->orderBy('pemeriksaan_ralan.tgl_perawatan')
                    ->orderBy('pemeriksaan_ralan.jam_rawat')
                    ->get();

                // Get pemeriksaan_ranap data
                $pemeriksaanRanap = DB::table('pemeriksaan_ranap')
                    ->leftJoin('pegawai', 'pemeriksaan_ranap.nip', '=', 'pegawai.nik')
                    ->select(
                        'pemeriksaan_ranap.*',
                        'pegawai.nama',
                        'pegawai.jbtn'
                    )
                    ->where('pemeriksaan_ranap.no_rawat', $reg->no_rawat)
                    ->orderBy('pemeriksaan_ranap.tgl_perawatan')
                    ->orderBy('pemeriksaan_ranap.jam_rawat')
                    ->get();

                // Merge both pemeriksaan data
                $allPemeriksaan = $pemeriksaanRalan->merge($pemeriksaanRanap);

                $result[] = [
                    'tgl_registrasi' => $reg->tgl_registrasi,
                    'no_rawat' => $reg->no_rawat,
                    'status_lanjut' => $reg->status_lanjut,
                    'pemeriksaan' => $allPemeriksaan
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil riwayat SOAPIE: ' . $e->getMessage()
            ], 500);
        }
    }
}
