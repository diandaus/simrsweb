<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaboratoriumController extends Controller
{
    /**
     * Get list of Pemeriksaan Lab PK
     */
    public function getPemeriksaanPK(Request $request)
    {
        $search = $request->input('search', '');

        $query = DB::table('jns_perawatan_lab')
            ->select(
                'jns_perawatan_lab.kd_jenis_prw',
                'jns_perawatan_lab.nm_perawatan',
                'jns_perawatan_lab.bagian_rs as total_byr',
                'jns_perawatan_lab.kategori',
                'jns_perawatan_lab.status'
            )
            ->where('jns_perawatan_lab.status', '1')
            ->where('jns_perawatan_lab.kategori', 'PK');

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('jns_perawatan_lab.nm_perawatan', 'like', "%{$search}%")
                  ->orWhere('jns_perawatan_lab.kd_jenis_prw', 'like', "%{$search}%");
            });
        }

        $data = $query->orderBy('jns_perawatan_lab.nm_perawatan')->get();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Get list of Pemeriksaan Lab PA
     */
    public function getPemeriksaanPA(Request $request)
    {
        $search = $request->input('search', '');

        $query = DB::table('jns_perawatan_labpa')
            ->select(
                'jns_perawatan_labpa.kd_jenis_prw',
                'jns_perawatan_labpa.nm_perawatan',
                'jns_perawatan_labpa.bagian_rs as total_byr',
                'jns_perawatan_labpa.kategori',
                'jns_perawatan_labpa.status'
            )
            ->where('jns_perawatan_labpa.status', '1');

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('jns_perawatan_labpa.nm_perawatan', 'like', "%{$search}%")
                  ->orWhere('jns_perawatan_labpa.kd_jenis_prw', 'like', "%{$search}%");
            });
        }

        $data = $query->orderBy('jns_perawatan_labpa.nm_perawatan')->get();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Get detail template pemeriksaan lab PK
     */
    public function getTemplatePK($kd_jenis_prw, Request $request)
    {
        $search = $request->input('search', '');

        $query = DB::table('template_laboratorium')
            ->select(
                'id_template',
                'Pemeriksaan',
                'satuan',
                'nilai_rujukan_ld',
                'nilai_rujukan_la',
                'nilai_rujukan_pd',
                'nilai_rujukan_pa',
                'urut'
            )
            ->where('kd_jenis_prw', $kd_jenis_prw);

        if (!empty($search)) {
            $query->where('Pemeriksaan', 'like', "%{$search}%");
        }

        $templates = $query->orderBy('urut')->get();

        // Format nilai rujukan
        $templates = $templates->map(function($item) {
            $nilai_rujukan = [];
            if (!empty($item->nilai_rujukan_ld)) {
                $nilai_rujukan[] = "LD : " . $item->nilai_rujukan_ld;
            }
            if (!empty($item->nilai_rujukan_la)) {
                $nilai_rujukan[] = "LA : " . $item->nilai_rujukan_la;
            }
            if (!empty($item->nilai_rujukan_pd)) {
                $nilai_rujukan[] = "PD : " . $item->nilai_rujukan_pd;
            }
            if (!empty($item->nilai_rujukan_pa)) {
                $nilai_rujukan[] = "PA : " . $item->nilai_rujukan_pa;
            }

            $item->nilai_rujukan = implode(', ', $nilai_rujukan);
            return $item;
        });

        return response()->json([
            'success' => true,
            'data' => $templates
        ]);
    }

    /**
     * Store Permintaan Lab (PK dan PA)
     */
    public function storePermintaan(Request $request)
    {
        $request->validate([
            'no_rawat' => 'required',
            'diagnosa_klinis' => 'required',
        ]);

        DB::beginTransaction();

        try {
            $no_rawat = $request->no_rawat;
            $tgl_permintaan = date('Y-m-d');
            $jam_permintaan = date('H:i:s');
            $diagnosa_klinis = $request->diagnosa_klinis;
            $informasi_tambahan = $request->informasi_tambahan ?? '';

            // Get dokter perujuk dari reg_periksa
            $reg = DB::table('reg_periksa')
                ->where('no_rawat', $no_rawat)
                ->first();

            if (!$reg) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data registrasi tidak ditemukan'
                ], 404);
            }

            $kd_dokter = $reg->kd_dokter;
            $status = $reg->status_lanjut; // Ralan atau Ranap

            $noorder_pk = null;
            $noorder_pa = null;

            // Simpan Permintaan Lab PK
            if (!empty($request->pemeriksaan_pk) && count($request->pemeriksaan_pk) > 0) {
                // Generate nomor order PK
                $noorder_pk = $this->generateNoOrderPK($tgl_permintaan);

                // Insert ke permintaan_lab
                DB::table('permintaan_lab')->insert([
                    'noorder' => $noorder_pk,
                    'no_rawat' => $no_rawat,
                    'tgl_permintaan' => $tgl_permintaan,
                    'jam_permintaan' => $jam_permintaan,
                    'tgl_sampel' => '0000-00-00',
                    'jam_sampel' => '00:00:00',
                    'tgl_hasil' => '0000-00-00',
                    'jam_hasil' => '00:00:00',
                    'dokter_perujuk' => $kd_dokter,
                    'status' => strtolower($status),
                    'informasi_tambahan' => $informasi_tambahan,
                    'diagnosa_klinis' => $diagnosa_klinis
                ]);

                // Insert detail pemeriksaan PK
                foreach ($request->pemeriksaan_pk as $pemeriksaan) {
                    // Insert ke permintaan_pemeriksaan_lab (hanya noorder dan kd_jenis_prw)
                    DB::table('permintaan_pemeriksaan_lab')->insert([
                        'noorder' => $noorder_pk,
                        'kd_jenis_prw' => $pemeriksaan['kd_jenis_prw']
                    ]);

                    // Get template detail jika ada
                    $templates = DB::table('template_laboratorium')
                        ->where('kd_jenis_prw', $pemeriksaan['kd_jenis_prw'])
                        ->orderBy('urut')
                        ->get();

                    foreach ($templates as $template) {
                        // Insert ke permintaan_detail_permintaan_lab
                        DB::table('permintaan_detail_permintaan_lab')->insert([
                            'noorder' => $noorder_pk,
                            'kd_jenis_prw' => $pemeriksaan['kd_jenis_prw'],
                            'id_template' => $template->id_template
                        ]);
                    }
                }
            }

            // Simpan Permintaan Lab PA
            if (!empty($request->pemeriksaan_pa) && count($request->pemeriksaan_pa) > 0) {
                // Generate nomor order PA
                $noorder_pa = $this->generateNoOrderPA($tgl_permintaan);

                // Insert ke permintaan_labpa
                DB::table('permintaan_labpa')->insert([
                    'noorder' => $noorder_pa,
                    'no_rawat' => $no_rawat,
                    'tgl_permintaan' => $tgl_permintaan,
                    'jam_permintaan' => $jam_permintaan,
                    'tgl_sampel' => '0000-00-00',
                    'jam_sampel' => '00:00:00',
                    'tgl_hasil' => '0000-00-00',
                    'jam_hasil' => '00:00:00',
                    'dokter_perujuk' => $kd_dokter,
                    'status' => strtolower($status),
                    'informasi_tambahan' => $informasi_tambahan,
                    'diagnosa_klinis' => $diagnosa_klinis,
                    'tgl_pengambilan' => '0000-00-00',
                    'diperoleh_dari' => '',
                    'lokasi_pengambilan' => '',
                    'diawetkan' => '',
                    'dilakukan' => '',
                    'tgl_PA' => '0000-00-00',
                    'nomorPA' => '',
                    'diagnosa_PA' => ''
                ]);

                // Insert detail pemeriksaan PA (hanya noorder dan kd_jenis_prw)
                foreach ($request->pemeriksaan_pa as $pemeriksaan) {
                    DB::table('permintaan_pemeriksaan_labpa')->insert([
                        'noorder' => $noorder_pa,
                        'kd_jenis_prw' => $pemeriksaan['kd_jenis_prw']
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Permintaan laboratorium berhasil disimpan',
                'noorder_pk' => $noorder_pk,
                'noorder_pa' => $noorder_pa
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan permintaan laboratorium: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Riwayat Permintaan Lab PK by No Rawat
     */
    public function getRiwayat($no_rawat)
    {
        try {
            $results = [];

            // Get permintaan Lab PK - hanya hari ini
            $permintaanPK = DB::table('permintaan_lab')
                ->join('reg_periksa', 'permintaan_lab.no_rawat', '=', 'reg_periksa.no_rawat')
                ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
                ->join('dokter', 'permintaan_lab.dokter_perujuk', '=', 'dokter.kd_dokter')
                ->join('poliklinik', 'reg_periksa.kd_poli', '=', 'poliklinik.kd_poli')
                ->join('penjab', 'reg_periksa.kd_pj', '=', 'penjab.kd_pj')
                ->select(
                    'permintaan_lab.noorder',
                    'permintaan_lab.no_rawat',
                    'reg_periksa.no_rkm_medis',
                    'pasien.nm_pasien',
                    'permintaan_lab.tgl_permintaan',
                    DB::raw("IF(permintaan_lab.jam_permintaan='00:00:00','',permintaan_lab.jam_permintaan) as jam_permintaan"),
                    DB::raw("IF(permintaan_lab.tgl_sampel='0000-00-00','',permintaan_lab.tgl_sampel) as tgl_sampel"),
                    DB::raw("IF(permintaan_lab.jam_sampel='00:00:00','',permintaan_lab.jam_sampel) as jam_sampel"),
                    DB::raw("IF(permintaan_lab.tgl_hasil='0000-00-00','',permintaan_lab.tgl_hasil) as tgl_hasil"),
                    DB::raw("IF(permintaan_lab.jam_hasil='00:00:00','',permintaan_lab.jam_hasil) as jam_hasil"),
                    'permintaan_lab.dokter_perujuk',
                    'dokter.nm_dokter',
                    'poliklinik.nm_poli',
                    'permintaan_lab.informasi_tambahan',
                    'permintaan_lab.diagnosa_klinis',
                    'reg_periksa.kd_pj',
                    'penjab.png_jawab'
                )
                ->where('permintaan_lab.no_rawat', $no_rawat)
                ->whereDate('permintaan_lab.tgl_permintaan', date('Y-m-d'))
                ->orderBy('permintaan_lab.tgl_permintaan', 'desc')
                ->orderBy('permintaan_lab.jam_permintaan', 'desc')
                ->get();

            foreach ($permintaanPK as $permintaan) {
                // Add header row
                $results[] = [
                    'type' => 'header',
                    'noorder' => $permintaan->noorder,
                    'no_rawat' => $permintaan->no_rawat,
                    'no_rkm_medis' => $permintaan->no_rkm_medis,
                    'nm_pasien' => $permintaan->nm_pasien,
                    'tgl_permintaan' => $permintaan->tgl_permintaan,
                    'jam_permintaan' => $permintaan->jam_permintaan,
                    'tgl_sampel' => $permintaan->tgl_sampel,
                    'jam_sampel' => $permintaan->jam_sampel,
                    'tgl_hasil' => $permintaan->tgl_hasil,
                    'jam_hasil' => $permintaan->jam_hasil,
                    'dokter_perujuk' => $permintaan->nm_dokter,
                    'nm_poli' => $permintaan->nm_poli,
                    'informasi_tambahan' => $permintaan->informasi_tambahan,
                    'diagnosa_klinis' => $permintaan->diagnosa_klinis,
                    'png_jawab' => $permintaan->png_jawab
                ];

                // Get pemeriksaan
                $pemeriksaan = DB::table('permintaan_pemeriksaan_lab')
                    ->join('jns_perawatan_lab', 'permintaan_pemeriksaan_lab.kd_jenis_prw', '=', 'jns_perawatan_lab.kd_jenis_prw')
                    ->select('permintaan_pemeriksaan_lab.kd_jenis_prw', 'jns_perawatan_lab.nm_perawatan')
                    ->where('permintaan_pemeriksaan_lab.noorder', $permintaan->noorder)
                    ->get();

                foreach ($pemeriksaan as $p) {
                    // Add pemeriksaan row
                    $results[] = [
                        'type' => 'pemeriksaan',
                        'nm_perawatan' => $p->nm_perawatan
                    ];

                    // Add pemeriksaan header row (for sub-columns)
                    $results[] = [
                        'type' => 'pemeriksaan-header'
                    ];

                    // Get detail template
                    $details = DB::table('permintaan_detail_permintaan_lab')
                        ->join('template_laboratorium', 'permintaan_detail_permintaan_lab.id_template', '=', 'template_laboratorium.id_template')
                        ->select(
                            'template_laboratorium.Pemeriksaan',
                            'template_laboratorium.satuan',
                            'template_laboratorium.nilai_rujukan_ld',
                            'template_laboratorium.nilai_rujukan_la',
                            'template_laboratorium.nilai_rujukan_pd',
                            'template_laboratorium.nilai_rujukan_pa'
                        )
                        ->where('permintaan_detail_permintaan_lab.kd_jenis_prw', $p->kd_jenis_prw)
                        ->where('permintaan_detail_permintaan_lab.noorder', $permintaan->noorder)
                        ->orderBy('template_laboratorium.urut')
                        ->get();

                    foreach ($details as $detail) {
                        $results[] = [
                            'type' => 'detail',
                            'pemeriksaan' => $detail->Pemeriksaan,
                            'satuan' => $detail->satuan,
                            'nilai_rujukan_ld' => $detail->nilai_rujukan_ld ?? '',
                            'nilai_rujukan_la' => $detail->nilai_rujukan_la ?? '',
                            'nilai_rujukan_pd' => $detail->nilai_rujukan_pd ?? '',
                            'nilai_rujukan_pa' => $detail->nilai_rujukan_pa ?? ''
                        ];
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => $results
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat riwayat: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Riwayat Permintaan Lab PA by No Rawat
     */
    public function getRiwayatPA($no_rawat)
    {
        try {
            $results = [];

            // Get permintaan Lab PA - hanya hari ini
            $permintaanPA = DB::table('permintaan_labpa')
                ->join('reg_periksa', 'permintaan_labpa.no_rawat', '=', 'reg_periksa.no_rawat')
                ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
                ->join('dokter', 'permintaan_labpa.dokter_perujuk', '=', 'dokter.kd_dokter')
                ->join('poliklinik', 'reg_periksa.kd_poli', '=', 'poliklinik.kd_poli')
                ->join('penjab', 'reg_periksa.kd_pj', '=', 'penjab.kd_pj')
                ->select(
                    'permintaan_labpa.noorder',
                    'permintaan_labpa.no_rawat',
                    'reg_periksa.no_rkm_medis',
                    'pasien.nm_pasien',
                    'permintaan_labpa.tgl_permintaan',
                    DB::raw("IF(permintaan_labpa.jam_permintaan='00:00:00','',permintaan_labpa.jam_permintaan) as jam_permintaan"),
                    DB::raw("IF(permintaan_labpa.tgl_sampel='0000-00-00','',permintaan_labpa.tgl_sampel) as tgl_sampel"),
                    DB::raw("IF(permintaan_labpa.jam_sampel='00:00:00','',permintaan_labpa.jam_sampel) as jam_sampel"),
                    DB::raw("IF(permintaan_labpa.tgl_hasil='0000-00-00','',permintaan_labpa.tgl_hasil) as tgl_hasil"),
                    DB::raw("IF(permintaan_labpa.jam_hasil='00:00:00','',permintaan_labpa.jam_hasil) as jam_hasil"),
                    'permintaan_labpa.dokter_perujuk',
                    'dokter.nm_dokter',
                    'poliklinik.nm_poli',
                    'permintaan_labpa.informasi_tambahan',
                    'permintaan_labpa.diagnosa_klinis',
                    'reg_periksa.kd_pj',
                    'penjab.png_jawab'
                )
                ->where('permintaan_labpa.no_rawat', $no_rawat)
                ->whereDate('permintaan_labpa.tgl_permintaan', date('Y-m-d'))
                ->orderBy('permintaan_labpa.tgl_permintaan', 'desc')
                ->orderBy('permintaan_labpa.jam_permintaan', 'desc')
                ->get();

            foreach ($permintaanPA as $permintaan) {
                // Add header row
                $results[] = [
                    'type' => 'header',
                    'noorder' => $permintaan->noorder,
                    'no_rawat' => $permintaan->no_rawat,
                    'no_rkm_medis' => $permintaan->no_rkm_medis,
                    'nm_pasien' => $permintaan->nm_pasien,
                    'tgl_permintaan' => $permintaan->tgl_permintaan,
                    'jam_permintaan' => $permintaan->jam_permintaan,
                    'tgl_sampel' => $permintaan->tgl_sampel,
                    'jam_sampel' => $permintaan->jam_sampel,
                    'tgl_hasil' => $permintaan->tgl_hasil,
                    'jam_hasil' => $permintaan->jam_hasil,
                    'dokter_perujuk' => $permintaan->nm_dokter,
                    'nm_poli' => $permintaan->nm_poli,
                    'informasi_tambahan' => $permintaan->informasi_tambahan,
                    'diagnosa_klinis' => $permintaan->diagnosa_klinis,
                    'png_jawab' => $permintaan->png_jawab
                ];

                // Get pemeriksaan PA
                $pemeriksaan = DB::table('permintaan_pemeriksaan_labpa')
                    ->join('jns_perawatan_labpa', 'permintaan_pemeriksaan_labpa.kd_jenis_prw', '=', 'jns_perawatan_labpa.kd_jenis_prw')
                    ->select(
                        'permintaan_pemeriksaan_labpa.kd_jenis_prw',
                        'jns_perawatan_labpa.nm_perawatan',
                        'jns_perawatan_labpa.nilai_rujukan_ld',
                        'jns_perawatan_labpa.nilai_rujukan_la',
                        'jns_perawatan_labpa.nilai_rujukan_pd',
                        'jns_perawatan_labpa.nilai_rujukan_pa'
                    )
                    ->where('permintaan_pemeriksaan_labpa.noorder', $permintaan->noorder)
                    ->get();

                foreach ($pemeriksaan as $p) {
                    // Add pemeriksaan row (category name)
                    $results[] = [
                        'type' => 'pemeriksaan',
                        'nm_perawatan' => $p->nm_perawatan
                    ];

                    // Add pemeriksaan header row (for sub-columns)
                    $results[] = [
                        'type' => 'pemeriksaan-header'
                    ];

                    // Add detail row (PA shows same as pemeriksaan)
                    $results[] = [
                        'type' => 'detail',
                        'nm_perawatan' => $p->nm_perawatan,
                        'nilai_rujukan_ld' => $p->nilai_rujukan_ld ?? '',
                        'nilai_rujukan_la' => $p->nilai_rujukan_la ?? '',
                        'nilai_rujukan_pd' => $p->nilai_rujukan_pd ?? '',
                        'nilai_rujukan_pa' => $p->nilai_rujukan_pa ?? ''
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => $results
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat riwayat PA: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate No Order PK (format: PK + YYYYMMDD + 4 digit)
     */
    private function generateNoOrderPK($tgl_permintaan)
    {
        $prefix = 'PK' . str_replace('-', '', $tgl_permintaan);

        $maxNumber = DB::table('permintaan_lab')
            ->where('tgl_permintaan', $tgl_permintaan)
            ->selectRaw('IFNULL(MAX(CONVERT(RIGHT(noorder, 4), SIGNED)), 0) as max_num')
            ->first();

        $nextNumber = $maxNumber->max_num + 1;

        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate No Order PA (format: PA + YYYYMMDD + 4 digit)
     */
    private function generateNoOrderPA($tgl_permintaan)
    {
        $prefix = 'PA' . str_replace('-', '', $tgl_permintaan);

        $maxNumber = DB::table('permintaan_labpa')
            ->where('tgl_permintaan', $tgl_permintaan)
            ->selectRaw('IFNULL(MAX(CONVERT(RIGHT(noorder, 4), SIGNED)), 0) as max_num')
            ->first();

        $nextNumber = $maxNumber->max_num + 1;

        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
