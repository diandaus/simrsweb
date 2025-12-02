<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ResepController extends Controller
{
    /**
     * Generate nomor resep
     */
    private function generateNoResep()
    {
        $today = date('Y-m-d');
        $prefix = date('Ymd'); // Format: YYYYMMDD

        // Get last resep number for today
        $lastResep = DB::table('resep_obat')
            ->where('no_resep', 'like', $prefix . '%')
            ->orderBy('no_resep', 'desc')
            ->first();

        if ($lastResep) {
            // Extract counter from last no_resep (last 4 digits)
            $counter = (int) substr($lastResep->no_resep, -4) + 1;
        } else {
            $counter = 1;
        }

        // Format: YYYYMMDDXXXX (14 characters)
        return $prefix . str_pad($counter, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Store complete resep (header + non racikan + racikan)
     */
    public function store(Request $request)
    {
        $request->validate([
            'no_rawat' => 'required',
            'non_racikan' => 'array',
            'non_racikan.*.kode_brng' => 'required_with:non_racikan',
            'non_racikan.*.jml' => 'required_with:non_racikan|numeric|min:0.1',
            'non_racikan.*.aturan_pakai' => 'required_with:non_racikan',
            'racikan' => 'array',
            'racikan.*.nama_racikan' => 'required_with:racikan',
            'racikan.*.metode_racik' => 'nullable',
            'racikan.*.jml_dr' => 'required_with:racikan|numeric|min:1',
            'racikan.*.aturan_pakai' => 'required_with:racikan',
            'racikan.*.detail' => 'required_with:racikan|array',
        ]);

        try {
            DB::beginTransaction();

            // Get kd_dokter - Priority:
            // 1. From request (if provided by frontend)
            // 2. From dpjp_ranap (for ranap patients)
            // 3. From reg_periksa (for ralan patients)
            // 4. From logged in user if they are a dokter
            $kd_dokter = $request->input('kd_dokter');

            if (!$kd_dokter) {
                // Check if ranap, get from dpjp_ranap
                $kd_dokter = DB::table('dpjp_ranap')
                    ->where('no_rawat', $request->no_rawat)
                    ->value('kd_dokter');
            }

            if (!$kd_dokter) {
                // Get from reg_periksa
                $kd_dokter = DB::table('reg_periksa')
                    ->where('no_rawat', $request->no_rawat)
                    ->value('kd_dokter');
            }

            if (!$kd_dokter) {
                // Last resort: check if logged in user is a dokter (using nip from user_web)
                $user = auth()->user();
                if ($user && $user->nip) {
                    $dokter = DB::table('dokter')
                        ->where('kd_dokter', $user->nip)
                        ->first();
                    if ($dokter) {
                        $kd_dokter = $user->nip;
                    }
                }
            }

            // Validate kd_dokter exists
            if (!$kd_dokter) {
                throw new \Exception('Dokter tidak ditemukan untuk pasien ini');
            }

            // Get status (ralan/ranap)
            $status = $this->getStatusPasien($request->no_rawat);

            // Generate no_resep
            $no_resep = $this->generateNoResep();

            $tgl_peresepan = date('Y-m-d');
            $jam_peresepan = date('H:i:s');

            // 1. Insert header resep_obat
            DB::table('resep_obat')->insert([
                'no_resep' => $no_resep,
                'tgl_perawatan' => '0000-00-00',
                'jam' => '00:00:00',
                'no_rawat' => $request->no_rawat,
                'kd_dokter' => $kd_dokter,
                'tgl_peresepan' => $tgl_peresepan,
                'jam_peresepan' => $jam_peresepan,
                'status' => $status,
                'tgl_penyerahan' => '0000-00-00',
                'jam_penyerahan' => '00:00:00'
            ]);

            // 2. Insert non racikan ke resep_dokter
            if (!empty($request->non_racikan)) {
                foreach ($request->non_racikan as $obat) {
                    // Get kapasitas obat
                    $kapasitas = DB::table('databarang')
                        ->where('kode_brng', $obat['kode_brng'])
                        ->value('kapasitas');

                    $kapasitas = $kapasitas ?: 1; // Default 1 if null

                    // Jumlah = jml yang diinput / kapasitas
                    $jml = $obat['jml'] / $kapasitas;

                    DB::table('resep_dokter')->insert([
                        'no_resep' => $no_resep,
                        'kode_brng' => $obat['kode_brng'],
                        'jml' => $jml,
                        'aturan_pakai' => $obat['aturan_pakai']
                    ]);
                }
            }

            // 3. Insert racikan ke resep_dokter_racikan dan detail
            if (!empty($request->racikan)) {
                foreach ($request->racikan as $index => $racikan) {
                    $no_racik = $index + 1; // Sequential number

                    // Get kd_racik from metode_racik table
                    $kd_racik = '';
                    if (!empty($racikan['metode_racik'])) {
                        $metode = DB::table('metode_racik')
                            ->where('nm_racik', $racikan['metode_racik'])
                            ->first();
                        $kd_racik = $metode ? $metode->kd_racik : '';
                    }

                    // Insert header racikan
                    DB::table('resep_dokter_racikan')->insert([
                        'no_resep' => $no_resep,
                        'no_racik' => $no_racik,
                        'nama_racik' => $racikan['nama_racikan'],
                        'kd_racik' => $kd_racik,
                        'jml_dr' => $racikan['jml_dr'],
                        'aturan_pakai' => $racikan['aturan_pakai'],
                        'keterangan' => $racikan['metode_racik'] ?? ''
                    ]);

                    // Insert detail racikan
                    foreach ($racikan['detail'] as $detail) {
                        // Parse kandungan (bisa angka atau pecahan)
                        $kandunganValue = $this->parseKandungan($detail['kandungan']);

                        DB::table('resep_dokter_racikan_detail')->insert([
                            'no_resep' => $no_resep,
                            'no_racik' => $no_racik,
                            'kode_brng' => $detail['kode_brng'],
                            'p1' => 1,
                            'p2' => 1,
                            'kandungan' => $detail['kandungan'],
                            'jml' => $detail['jml']
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Resep berhasil disimpan',
                'no_resep' => $no_resep
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Store resep error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan resep: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Parse kandungan (support angka dan pecahan)
     */
    private function parseKandungan($kandungan)
    {
        $kandunganStr = trim($kandungan);

        if (strpos($kandunganStr, '/') !== false) {
            // Pecahan
            $parts = explode('/', $kandunganStr);
            if (count($parts) === 2) {
                $numerator = floatval($parts[0]);
                $denominator = floatval($parts[1]);
                if ($denominator != 0) {
                    return $numerator / $denominator;
                }
            }
        }

        return floatval($kandunganStr);
    }

    /**
     * Get status pasien (ralan/ranap)
     */
    private function getStatusPasien($no_rawat)
    {
        $regPeriksa = DB::table('reg_periksa')
            ->where('no_rawat', $no_rawat)
            ->first();

        return $regPeriksa ? $regPeriksa->status_lanjut : 'ralan';
    }

    /**
     * Store resep non racikan (legacy - deprecated)
     */
    public function storeNonRacikan(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Endpoint ini sudah deprecated. Gunakan POST /resep untuk menyimpan resep lengkap.'
        ], 400);
    }

    /**
     * Store resep racikan (legacy - deprecated)
     */
    public function storeRacikan(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Endpoint ini sudah deprecated. Gunakan POST /resep untuk menyimpan resep lengkap.'
        ], 400);
    }

    /**
     * Get resep by no_rawat
     */
    public function getResep($no_rawat)
    {
        try {
            // Get all resep for this no_rawat
            $resepList = DB::table('resep_obat')
                ->where('no_rawat', $no_rawat)
                ->orderBy('tgl_peresepan', 'desc')
                ->orderBy('jam_peresepan', 'desc')
                ->get();

            $result = [];

            foreach ($resepList as $resep) {
                // Get non racikan
                $nonRacikan = DB::table('resep_dokter')
                    ->join('databarang', 'resep_dokter.kode_brng', '=', 'databarang.kode_brng')
                    ->select(
                        'resep_dokter.*',
                        'databarang.nama_brng',
                        'databarang.kode_sat',
                        'databarang.kapasitas'
                    )
                    ->where('resep_dokter.no_resep', $resep->no_resep)
                    ->get();

                // Get racikan
                $racikan = DB::table('resep_dokter_racikan')
                    ->leftJoin('metode_racik', 'resep_dokter_racikan.kd_racik', '=', 'metode_racik.kd_racik')
                    ->select(
                        'resep_dokter_racikan.*',
                        'metode_racik.nm_racik as metode_racik'
                    )
                    ->where('no_resep', $resep->no_resep)
                    ->get();

                foreach ($racikan as $r) {
                    $r->detail = DB::table('resep_dokter_racikan_detail')
                        ->join('databarang', 'resep_dokter_racikan_detail.kode_brng', '=', 'databarang.kode_brng')
                        ->select(
                            'resep_dokter_racikan_detail.*',
                            'databarang.nama_brng',
                            'databarang.kode_sat',
                            'databarang.kapasitas'
                        )
                        ->where('resep_dokter_racikan_detail.no_resep', $r->no_resep)
                        ->where('resep_dokter_racikan_detail.no_racik', $r->no_racik)
                        ->get();
                }

                $result[] = [
                    'no_resep' => $resep->no_resep,
                    'tgl_peresepan' => $resep->tgl_peresepan,
                    'jam_peresepan' => $resep->jam_peresepan,
                    'kd_dokter' => $resep->kd_dokter,
                    'status' => $resep->status,
                    'non_racikan' => $nonRacikan,
                    'racikan' => $racikan
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data resep: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get riwayat resep by no_rkm_medis (history across all visits)
     */
    public function getRiwayatResep($no_rkm_medis)
    {
        try {
            // Get all resep for this patient (following Java code logic)
            $resepList = DB::table('resep_obat')
                ->join('reg_periksa', 'resep_obat.no_rawat', '=', 'reg_periksa.no_rawat')
                ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
                ->join('dokter', 'resep_obat.kd_dokter', '=', 'dokter.kd_dokter')
                ->select(
                    'resep_obat.no_resep',
                    'resep_obat.tgl_peresepan',
                    'resep_obat.jam_peresepan',
                    'resep_obat.no_rawat',
                    'pasien.no_rkm_medis',
                    'pasien.nm_pasien',
                    'resep_obat.kd_dokter',
                    'dokter.nm_dokter',
                    DB::raw("if(resep_obat.tgl_perawatan='0000-00-00','Belum Terlayani','Sudah Terlayani') as status"),
                    DB::raw("resep_obat.status as status_asal")
                )
                ->where('resep_obat.tgl_peresepan', '<>', '0000-00-00')
                ->where('pasien.no_rkm_medis', $no_rkm_medis)
                ->orderBy('resep_obat.tgl_peresepan', 'desc')
                ->orderBy('resep_obat.jam_peresepan', 'desc')
                ->get();

            $result = [];

            foreach ($resepList as $resep) {
                // Get non racikan
                $nonRacikan = DB::table('resep_dokter')
                    ->join('databarang', 'resep_dokter.kode_brng', '=', 'databarang.kode_brng')
                    ->select(
                        'databarang.kode_brng',
                        'databarang.nama_brng',
                        'resep_dokter.jml',
                        'databarang.kode_sat',
                        'resep_dokter.aturan_pakai'
                    )
                    ->where('resep_dokter.no_resep', $resep->no_resep)
                    ->orderBy('databarang.kode_brng')
                    ->get();

                // Get racikan
                $racikan = DB::table('resep_dokter_racikan')
                    ->leftJoin('metode_racik', 'resep_dokter_racikan.kd_racik', '=', 'metode_racik.kd_racik')
                    ->select(
                        'resep_dokter_racikan.no_racik',
                        'resep_dokter_racikan.nama_racik',
                        'resep_dokter_racikan.kd_racik',
                        'metode_racik.nm_racik as metode',
                        'resep_dokter_racikan.jml_dr',
                        'resep_dokter_racikan.aturan_pakai',
                        'resep_dokter_racikan.keterangan'
                    )
                    ->where('resep_dokter_racikan.no_resep', $resep->no_resep)
                    ->get();

                foreach ($racikan as $r) {
                    $r->detail = DB::table('resep_dokter_racikan_detail')
                        ->join('databarang', 'resep_dokter_racikan_detail.kode_brng', '=', 'databarang.kode_brng')
                        ->select(
                            'databarang.kode_brng',
                            'databarang.nama_brng',
                            'resep_dokter_racikan_detail.jml',
                            'databarang.kode_sat'
                        )
                        ->where('resep_dokter_racikan_detail.no_resep', $resep->no_resep)
                        ->where('resep_dokter_racikan_detail.no_racik', $r->no_racik)
                        ->orderBy('databarang.kode_brng')
                        ->get();
                }

                $result[] = [
                    'no_resep' => $resep->no_resep,
                    'tgl_peresepan' => $resep->tgl_peresepan,
                    'jam_peresepan' => $resep->jam_peresepan,
                    'no_rawat' => $resep->no_rawat,
                    'no_rkm_medis' => $resep->no_rkm_medis,
                    'nm_pasien' => $resep->nm_pasien,
                    'nm_dokter' => $resep->nm_dokter,
                    'kd_dokter' => $resep->kd_dokter,
                    'status' => $resep->status,
                    'status_asal' => str_replace(['ralan', 'ranap'], ['Rawat Jalan', 'Rawat Inap'], $resep->status_asal),
                    'non_racikan' => $nonRacikan,
                    'racikan' => $racikan
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            \Log::error('getRiwayatResep error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil riwayat resep: ' . $e->getMessage()
            ], 500);
        }
    }
}
