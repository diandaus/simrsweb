<?php

namespace App\Services;

use App\Helpers\HybridWebHelper;
use Illuminate\Support\Facades\DB;

class RiwayatPerawatanService
{
    public function getLegacyDetail(string $no_rawat): array
    {
        $riwayat = $this->buildLegacyRiwayat($no_rawat);
        $total = $this->calculateLegacyTotal($riwayat);

        return [
            'riwayat' => $riwayat,
            'total_biaya' => $total,
        ];
    }

    public function getStructuredDetail(string $no_rawat, ?array $kunjunganMeta = null): array
    {
        $legacy = $this->getLegacyDetail($no_rawat);
        $sections = $this->mapLegacySections($legacy['riwayat']);
        $meta = $kunjunganMeta ?? $this->fetchKunjunganMeta($no_rawat);
        $billing = $this->calculateStructuredTotals($sections);

        return [
            'no_rawat' => $no_rawat,
            'metadata' => $meta,
            'sections' => $sections,
            'billing' => $billing,
            'legacy' => $legacy['riwayat'],
        ];
    }

    public function getTimelineByPasien(string $no_rkm_medis): array
    {
        $kunjungan = DB::table('reg_periksa')
            ->leftJoin('dokter', 'reg_periksa.kd_dokter', '=', 'dokter.kd_dokter')
            ->leftJoin('poliklinik', 'reg_periksa.kd_poli', '=', 'poliklinik.kd_poli')
            ->leftJoin('penjab', 'reg_periksa.kd_pj', '=', 'penjab.kd_pj')
            ->where('reg_periksa.no_rkm_medis', $no_rkm_medis)
            ->select(
                'reg_periksa.no_rawat',
                'reg_periksa.tgl_registrasi',
                'reg_periksa.jam_reg',
                'reg_periksa.status_lanjut',
                DB::raw('penjab.png_jawab as penanggung_jawab'),
                'dokter.nm_dokter',
                'poliklinik.nm_poli'
            )
            ->orderBy('reg_periksa.tgl_registrasi', 'desc')
            ->orderBy('reg_periksa.jam_reg', 'desc')
            ->get();

        if ($kunjungan->isEmpty()) {
            return [
                'patient' => $this->fetchPatientMeta($no_rkm_medis),
                'summary' => [
                    'total_kunjungan' => 0,
                    'total_biaya' => 0,
                ],
                'kunjungan' => [],
            ];
        }

        $timeline = [];
        $grandTotal = 0;
        $totalKunjungan = $kunjungan->count();

        foreach ($kunjungan as $idx => $row) {
            $meta = [
                'no_rawat' => $row->no_rawat,
                'tgl_registrasi' => $row->tgl_registrasi,
                'jam_reg' => $row->jam_reg,
                'nm_dokter' => $row->nm_dokter,
                'nm_poli' => $row->nm_poli,
                'status_lanjut' => $row->status_lanjut,
                'penanggung_jawab' => $row->penanggung_jawab,
                'kunjungan_ke' => $totalKunjungan - $idx,
            ];

            $detail = $this->getStructuredDetail($row->no_rawat, $meta);

            $timeline[] = [
                'summary' => $meta,
                'sections' => $detail['sections'],
                'billing' => $detail['billing'],
                'legacy' => $detail['legacy'],
            ];

            $grandTotal += $detail['billing']['grand_total'];
        }

        return [
            'patient' => $this->fetchPatientMeta($no_rkm_medis),
            'summary' => [
                'total_kunjungan' => $totalKunjungan,
                'total_biaya' => $grandTotal,
            ],
            'kunjungan' => $timeline,
        ];
    }

    protected function fetchPatientMeta(string $no_rkm_medis): ?array
    {
        $patient = DB::table('pasien')
            ->leftJoin('bahasa_pasien', 'bahasa_pasien.id', '=', 'pasien.bahasa_pasien')
            ->leftJoin('cacat_fisik', 'cacat_fisik.id', '=', 'pasien.cacat_fisik')
            ->leftJoin('kelurahan', 'pasien.kd_kel', '=', 'kelurahan.kd_kel')
            ->leftJoin('kecamatan', 'pasien.kd_kec', '=', 'kecamatan.kd_kec')
            ->leftJoin('kabupaten', 'pasien.kd_kab', '=', 'kabupaten.kd_kab')
            ->where('pasien.no_rkm_medis', $no_rkm_medis)
            ->select(
                'pasien.no_rkm_medis',
                'pasien.nm_pasien',
                'pasien.jk',
                'pasien.tmp_lahir',
                'pasien.tgl_lahir',
                'pasien.agama',
                'bahasa_pasien.nama_bahasa',
                'cacat_fisik.nama_cacat',
                'pasien.gol_darah',
                'pasien.nm_ibu',
                'pasien.stts_nikah',
                'pasien.pnd',
                DB::raw("CONCAT(pasien.alamat, ', ', COALESCE(kelurahan.nm_kel, ''), ', ', COALESCE(kecamatan.nm_kec, ''), ', ', COALESCE(kabupaten.nm_kab, '')) as alamat"),
                'pasien.pekerjaan'
            )
            ->first();

        return $patient ? (array) $patient : null;
    }

    protected function fetchKunjunganMeta(string $no_rawat): ?array
    {
        $row = DB::table('reg_periksa')
            ->leftJoin('dokter', 'reg_periksa.kd_dokter', '=', 'dokter.kd_dokter')
            ->leftJoin('poliklinik', 'reg_periksa.kd_poli', '=', 'poliklinik.kd_poli')
            ->leftJoin('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->leftJoin('penjab', 'reg_periksa.kd_pj', '=', 'penjab.kd_pj')
            ->select(
                'reg_periksa.no_rawat',
                'reg_periksa.no_rkm_medis',
                'reg_periksa.tgl_registrasi',
                'reg_periksa.jam_reg',
                'reg_periksa.status_lanjut',
                DB::raw('penjab.png_jawab as penanggung_jawab'),
                'dokter.nm_dokter',
                'poliklinik.nm_poli',
                'pasien.nm_pasien'
            )
            ->where('reg_periksa.no_rawat', $no_rawat)
            ->first();

        return $row ? (array) $row : null;
    }

    protected function buildLegacyRiwayat(string $no_rawat): array
    {
        $riwayat = [];

        $riwayat['rawat_jalan_dokter'] = DB::table('rawat_jl_dr')
            ->join('jns_perawatan', 'rawat_jl_dr.kd_jenis_prw', '=', 'jns_perawatan.kd_jenis_prw')
            ->join('dokter', 'rawat_jl_dr.kd_dokter', '=', 'dokter.kd_dokter')
            ->where('rawat_jl_dr.no_rawat', $no_rawat)
            ->select(
                'rawat_jl_dr.tgl_perawatan',
                'rawat_jl_dr.jam_rawat',
                'rawat_jl_dr.kd_jenis_prw',
                'jns_perawatan.nm_perawatan',
                'dokter.nm_dokter',
                'rawat_jl_dr.biaya_rawat'
            )
            ->orderBy('rawat_jl_dr.tgl_perawatan')
            ->orderBy('rawat_jl_dr.jam_rawat')
            ->get();

        $riwayat['rawat_jalan_paramedis'] = DB::table('rawat_jl_pr')
            ->join('jns_perawatan', 'rawat_jl_pr.kd_jenis_prw', '=', 'jns_perawatan.kd_jenis_prw')
            ->join('petugas', 'rawat_jl_pr.nip', '=', 'petugas.nip')
            ->where('rawat_jl_pr.no_rawat', $no_rawat)
            ->select(
                'rawat_jl_pr.tgl_perawatan',
                'rawat_jl_pr.jam_rawat',
                'rawat_jl_pr.kd_jenis_prw',
                'jns_perawatan.nm_perawatan',
                'petugas.nama',
                'rawat_jl_pr.biaya_rawat'
            )
            ->orderBy('rawat_jl_pr.tgl_perawatan')
            ->orderBy('rawat_jl_pr.jam_rawat')
            ->get();

        $riwayat['rawat_jalan_dokter_paramedis'] = DB::table('rawat_jl_drpr')
            ->join('jns_perawatan', 'rawat_jl_drpr.kd_jenis_prw', '=', 'jns_perawatan.kd_jenis_prw')
            ->join('dokter', 'rawat_jl_drpr.kd_dokter', '=', 'dokter.kd_dokter')
            ->join('petugas', 'rawat_jl_drpr.nip', '=', 'petugas.nip')
            ->where('rawat_jl_drpr.no_rawat', $no_rawat)
            ->select(
                'rawat_jl_drpr.tgl_perawatan',
                'rawat_jl_drpr.jam_rawat',
                'rawat_jl_drpr.kd_jenis_prw',
                'jns_perawatan.nm_perawatan',
                'dokter.nm_dokter',
                'petugas.nama',
                'rawat_jl_drpr.biaya_rawat'
            )
            ->orderBy('rawat_jl_drpr.tgl_perawatan')
            ->orderBy('rawat_jl_drpr.jam_rawat')
            ->get();

        $riwayat['rawat_inap_dokter'] = DB::table('rawat_inap_dr')
            ->join('jns_perawatan_inap', 'rawat_inap_dr.kd_jenis_prw', '=', 'jns_perawatan_inap.kd_jenis_prw')
            ->join('dokter', 'rawat_inap_dr.kd_dokter', '=', 'dokter.kd_dokter')
            ->where('rawat_inap_dr.no_rawat', $no_rawat)
            ->select(
                'rawat_inap_dr.tgl_perawatan',
                'rawat_inap_dr.jam_rawat',
                'rawat_inap_dr.kd_jenis_prw',
                'jns_perawatan_inap.nm_perawatan',
                'dokter.nm_dokter',
                'rawat_inap_dr.biaya_rawat'
            )
            ->orderBy('rawat_inap_dr.tgl_perawatan')
            ->orderBy('rawat_inap_dr.jam_rawat')
            ->get();

        $riwayat['rawat_inap_paramedis'] = DB::table('rawat_inap_pr')
            ->join('jns_perawatan_inap', 'rawat_inap_pr.kd_jenis_prw', '=', 'jns_perawatan_inap.kd_jenis_prw')
            ->join('petugas', 'rawat_inap_pr.nip', '=', 'petugas.nip')
            ->where('rawat_inap_pr.no_rawat', $no_rawat)
            ->select(
                'rawat_inap_pr.tgl_perawatan',
                'rawat_inap_pr.jam_rawat',
                'rawat_inap_pr.kd_jenis_prw',
                'jns_perawatan_inap.nm_perawatan',
                'petugas.nama',
                'rawat_inap_pr.biaya_rawat'
            )
            ->orderBy('rawat_inap_pr.tgl_perawatan')
            ->orderBy('rawat_inap_pr.jam_rawat')
            ->get();

        $riwayat['rawat_inap_dokter_paramedis'] = DB::table('rawat_inap_drpr')
            ->join('jns_perawatan_inap', 'rawat_inap_drpr.kd_jenis_prw', '=', 'jns_perawatan_inap.kd_jenis_prw')
            ->join('dokter', 'rawat_inap_drpr.kd_dokter', '=', 'dokter.kd_dokter')
            ->join('petugas', 'rawat_inap_drpr.nip', '=', 'petugas.nip')
            ->where('rawat_inap_drpr.no_rawat', $no_rawat)
            ->select(
                'rawat_inap_drpr.tgl_perawatan',
                'rawat_inap_drpr.jam_rawat',
                'rawat_inap_drpr.kd_jenis_prw',
                'jns_perawatan_inap.nm_perawatan',
                'dokter.nm_dokter',
                'petugas.nama',
                'rawat_inap_drpr.biaya_rawat'
            )
            ->orderBy('rawat_inap_drpr.tgl_perawatan')
            ->orderBy('rawat_inap_drpr.jam_rawat')
            ->get();

        $riwayat['kamar_inap'] = DB::table('kamar_inap')
            ->join('kamar', 'kamar_inap.kd_kamar', '=', 'kamar.kd_kamar')
            ->join('bangsal', 'kamar.kd_bangsal', '=', 'bangsal.kd_bangsal')
            ->where('kamar_inap.no_rawat', $no_rawat)
            ->select(
                'kamar_inap.kd_kamar',
                'bangsal.nm_bangsal',
                'kamar_inap.tgl_masuk',
                'kamar_inap.jam_masuk',
                'kamar_inap.tgl_keluar',
                'kamar_inap.jam_keluar',
                'kamar_inap.lama',
                'kamar_inap.stts_pulang',
                'kamar_inap.ttl_biaya'
            )
            ->orderBy('kamar_inap.tgl_masuk')
            ->orderBy('kamar_inap.jam_masuk')
            ->get();

        $riwayat['radiologi'] = DB::table('periksa_radiologi')
            ->join('jns_perawatan_radiologi', 'periksa_radiologi.kd_jenis_prw', '=', 'jns_perawatan_radiologi.kd_jenis_prw')
            ->join('petugas', 'periksa_radiologi.nip', '=', 'petugas.nip')
            ->join('dokter', 'periksa_radiologi.kd_dokter', '=', 'dokter.kd_dokter')
            ->where('periksa_radiologi.no_rawat', $no_rawat)
            ->select(
                'periksa_radiologi.tgl_periksa',
                'periksa_radiologi.jam',
                'periksa_radiologi.kd_jenis_prw',
                'jns_perawatan_radiologi.nm_perawatan',
                'dokter.nm_dokter',
                'petugas.nama',
                'periksa_radiologi.biaya',
                'periksa_radiologi.proyeksi',
                'periksa_radiologi.kV',
                'periksa_radiologi.mAS',
                'periksa_radiologi.FFD',
                'periksa_radiologi.BSF',
                'periksa_radiologi.inak',
                'periksa_radiologi.jml_penyinaran',
                'periksa_radiologi.dosis'
            )
            ->orderBy('periksa_radiologi.tgl_periksa')
            ->orderBy('periksa_radiologi.jam')
            ->get();

        $riwayat['hasil_radiologi'] = DB::table('hasil_radiologi')
            ->where('no_rawat', $no_rawat)
            ->select('tgl_periksa', 'jam', 'hasil')
            ->orderBy('tgl_periksa')
            ->orderBy('jam')
            ->get();

        $riwayat['gambar_radiologi'] = DB::table('gambar_radiologi')
            ->where('no_rawat', $no_rawat)
            ->select('tgl_periksa', 'jam', 'lokasi_gambar')
            ->orderBy('tgl_periksa')
            ->orderBy('jam')
            ->get()
            ->map(function ($item) {
                $item->gambar_url = $item->lokasi_gambar
                    ? HybridWebHelper::getRadiologiImageUrl($item->lokasi_gambar)
                    : null;

                return $item;
            });

        $labPKMB = DB::table('periksa_lab')
            ->where('periksa_lab.no_rawat', $no_rawat)
            ->where('periksa_lab.kategori', '<>', 'PA')
            ->select('tgl_periksa', 'jam')
            ->groupBy('no_rawat', 'tgl_periksa', 'jam')
            ->orderBy('tgl_periksa')
            ->orderBy('jam')
            ->get();

        $riwayat['laboratorium_pkmb'] = [];
        foreach ($labPKMB as $lab) {
            $pemeriksaan = DB::table('periksa_lab')
                ->join('jns_perawatan_lab', 'periksa_lab.kd_jenis_prw', '=', 'jns_perawatan_lab.kd_jenis_prw')
                ->join('petugas', 'periksa_lab.nip', '=', 'petugas.nip')
                ->join('dokter', 'periksa_lab.kd_dokter', '=', 'dokter.kd_dokter')
                ->where('periksa_lab.no_rawat', $no_rawat)
                ->where('periksa_lab.kategori', '<>', 'PA')
                ->where('periksa_lab.tgl_periksa', $lab->tgl_periksa)
                ->where('periksa_lab.jam', $lab->jam)
                ->select(
                    'periksa_lab.kd_jenis_prw',
                    'jns_perawatan_lab.nm_perawatan',
                    'petugas.nama',
                    'periksa_lab.biaya',
                    'dokter.nm_dokter'
                )
                ->get();

            foreach ($pemeriksaan as $p) {
                $p->detail = DB::table('detail_periksa_lab')
                    ->join('template_laboratorium', 'detail_periksa_lab.id_template', '=', 'template_laboratorium.id_template')
                    ->where('detail_periksa_lab.no_rawat', $no_rawat)
                    ->where('detail_periksa_lab.kd_jenis_prw', $p->kd_jenis_prw)
                    ->where('detail_periksa_lab.tgl_periksa', $lab->tgl_periksa)
                    ->where('detail_periksa_lab.jam', $lab->jam)
                    ->select(
                        'template_laboratorium.Pemeriksaan',
                        'detail_periksa_lab.nilai',
                        'template_laboratorium.satuan',
                        'detail_periksa_lab.nilai_rujukan',
                        'detail_periksa_lab.biaya_item',
                        'detail_periksa_lab.keterangan'
                    )
                    ->orderBy('template_laboratorium.urut')
                    ->get();

                $p->saran_kesan = DB::table('saran_kesan_lab')
                    ->where('no_rawat', $no_rawat)
                    ->where('tgl_periksa', $lab->tgl_periksa)
                    ->where('jam', $lab->jam)
                    ->select('saran', 'kesan')
                    ->first();
            }

            $riwayat['laboratorium_pkmb'][] = [
                'tgl_periksa' => $lab->tgl_periksa,
                'jam' => $lab->jam,
                'pemeriksaan' => $pemeriksaan,
            ];
        }

        $riwayat['laboratorium_pa'] = DB::table('periksa_lab')
            ->join('jns_perawatan_lab', 'periksa_lab.kd_jenis_prw', '=', 'jns_perawatan_lab.kd_jenis_prw')
            ->join('petugas', 'periksa_lab.nip', '=', 'petugas.nip')
            ->join('dokter', 'periksa_lab.kd_dokter', '=', 'dokter.kd_dokter')
            ->where('periksa_lab.no_rawat', $no_rawat)
            ->where('periksa_lab.kategori', 'PA')
            ->select(
                'periksa_lab.tgl_periksa',
                'periksa_lab.jam',
                'periksa_lab.kd_jenis_prw',
                'jns_perawatan_lab.nm_perawatan',
                'petugas.nama',
                'periksa_lab.biaya',
                'dokter.nm_dokter'
            )
            ->orderBy('periksa_lab.tgl_periksa')
            ->orderBy('periksa_lab.jam')
            ->get()
            ->map(function ($pa) use ($no_rawat) {
                $pa->detail = DB::table('detail_periksa_labpa')
                    ->where('no_rawat', $no_rawat)
                    ->where('kd_jenis_prw', $pa->kd_jenis_prw)
                    ->where('tgl_periksa', $pa->tgl_periksa)
                    ->where('jam', $pa->jam)
                    ->select(
                        'diagnosa_klinik',
                        'makroskopik',
                        'mikroskopik',
                        'kesimpulan',
                        'kesan'
                    )
                    ->first();

                $gambarFilename = DB::table('detail_periksa_labpa_gambar')
                    ->where('no_rawat', $no_rawat)
                    ->where('kd_jenis_prw', $pa->kd_jenis_prw)
                    ->where('tgl_periksa', $pa->tgl_periksa)
                    ->where('jam', $pa->jam)
                    ->value('photo');

                $pa->gambar = $gambarFilename;
                $pa->gambar_url = $gambarFilename ? HybridWebHelper::getLabPAImageUrl($gambarFilename) : null;

                return $pa;
            });

        $riwayat['pemberian_obat'] = DB::table('detail_pemberian_obat')
            ->join('databarang', 'detail_pemberian_obat.kode_brng', '=', 'databarang.kode_brng')
            ->where('detail_pemberian_obat.no_rawat', $no_rawat)
            ->select(
                'detail_pemberian_obat.tgl_perawatan',
                'detail_pemberian_obat.jam',
                'detail_pemberian_obat.kode_brng',
                'databarang.nama_brng',
                'databarang.kode_sat',
                'detail_pemberian_obat.jml',
                'detail_pemberian_obat.total'
            )
            ->orderBy('detail_pemberian_obat.tgl_perawatan')
            ->orderBy('detail_pemberian_obat.jam')
            ->get()
            ->map(function ($obat) use ($no_rawat) {
                $obat->aturan_pakai = DB::table('aturan_pakai')
                    ->where('no_rawat', $no_rawat)
                    ->where('tgl_perawatan', $obat->tgl_perawatan)
                    ->where('jam', $obat->jam)
                    ->where('kode_brng', $obat->kode_brng)
                    ->value('aturan');

                return $obat;
            });

        $riwayat['resep_pulang'] = DB::table('resep_pulang')
            ->join('databarang', 'resep_pulang.kode_brng', '=', 'databarang.kode_brng')
            ->where('resep_pulang.no_rawat', $no_rawat)
            ->select(
                'resep_pulang.kode_brng',
                'databarang.nama_brng',
                'resep_pulang.dosis',
                'resep_pulang.jml_barang',
                'databarang.kode_sat',
                'resep_pulang.total'
            )
            ->orderBy('databarang.nama_brng')
            ->get();

        $riwayat['ppn_obat'] = DB::table('billing')
            ->where('no_rawat', $no_rawat)
            ->where('nm_perawatan', 'PPN Obat')
            ->where('status', 'Obat')
            ->value('totalbiaya') ?? 0;

        $riwayat['triase_primer'] = DB::table('data_triase_igdprimer')
            ->join('data_triase_igd', 'data_triase_igd.no_rawat', '=', 'data_triase_igdprimer.no_rawat')
            ->join('master_triase_macam_kasus', 'data_triase_igd.kode_kasus', '=', 'master_triase_macam_kasus.kode_kasus')
            ->join('pegawai', 'data_triase_igdprimer.nik', '=', 'pegawai.nik')
            ->where('data_triase_igd.no_rawat', $no_rawat)
            ->select(
                'data_triase_igdprimer.keluhan_utama',
                'data_triase_igdprimer.kebutuhan_khusus',
                'data_triase_igdprimer.catatan',
                'data_triase_igdprimer.plan',
                'data_triase_igdprimer.tanggaltriase',
                'data_triase_igdprimer.nik',
                'data_triase_igd.tekanan_darah',
                'data_triase_igd.nadi',
                'data_triase_igd.pernapasan',
                'data_triase_igd.suhu',
                'data_triase_igd.saturasi_o2',
                'data_triase_igd.nyeri',
                'data_triase_igd.cara_masuk',
                'data_triase_igd.alat_transportasi',
                'data_triase_igd.alasan_kedatangan',
                'data_triase_igd.keterangan_kedatangan',
                'data_triase_igd.kode_kasus',
                'master_triase_macam_kasus.macam_kasus',
                'pegawai.nama'
            )
            ->first();

        if ($riwayat['triase_primer']) {
            $riwayat['triase_primer']->skala1 = $this->fetchTriaseSkala($no_rawat, 1);
            $riwayat['triase_primer']->skala2 = $this->fetchTriaseSkala($no_rawat, 2);
        }

        $riwayat['triase_sekunder'] = DB::table('data_triase_igdsekunder')
            ->join('data_triase_igd', 'data_triase_igd.no_rawat', '=', 'data_triase_igdsekunder.no_rawat')
            ->join('master_triase_macam_kasus', 'data_triase_igd.kode_kasus', '=', 'master_triase_macam_kasus.kode_kasus')
            ->join('pegawai', 'data_triase_igdsekunder.nik', '=', 'pegawai.nik')
            ->where('data_triase_igd.no_rawat', $no_rawat)
            ->select(
                'data_triase_igdsekunder.anamnesa_singkat',
                'data_triase_igdsekunder.catatan',
                'data_triase_igdsekunder.plan',
                'data_triase_igdsekunder.tanggaltriase',
                'data_triase_igdsekunder.nik',
                'data_triase_igd.tekanan_darah',
                'data_triase_igd.nadi',
                'data_triase_igd.pernapasan',
                'data_triase_igd.suhu',
                'data_triase_igd.saturasi_o2',
                'data_triase_igd.nyeri',
                'data_triase_igd.cara_masuk',
                'data_triase_igd.alat_transportasi',
                'data_triase_igd.alasan_kedatangan',
                'data_triase_igd.keterangan_kedatangan',
                'data_triase_igd.kode_kasus',
                'master_triase_macam_kasus.macam_kasus',
                'pegawai.nama'
            )
            ->first();

        if ($riwayat['triase_sekunder']) {
            $riwayat['triase_sekunder']->skala3 = $this->fetchTriaseSkala($no_rawat, 3);
            $riwayat['triase_sekunder']->skala4 = $this->fetchTriaseSkala($no_rawat, 4);
            $riwayat['triase_sekunder']->skala5 = $this->fetchTriaseSkala($no_rawat, 5);
        }

        $riwayat['asuhan_medis_igd'] = DB::table('penilaian_medis_igd')
            ->join('dokter', 'penilaian_medis_igd.kd_dokter', '=', 'dokter.kd_dokter')
            ->where('penilaian_medis_igd.no_rawat', $no_rawat)
            ->select(
                'penilaian_medis_igd.tanggal',
                'penilaian_medis_igd.kd_dokter',
                'dokter.nm_dokter',
                'penilaian_medis_igd.anamnesis',
                'penilaian_medis_igd.hubungan',
                'penilaian_medis_igd.keluhan_utama',
                'penilaian_medis_igd.rps',
                'penilaian_medis_igd.rpk',
                'penilaian_medis_igd.rpd',
                'penilaian_medis_igd.rpo',
                'penilaian_medis_igd.alergi',
                'penilaian_medis_igd.keadaan',
                'penilaian_medis_igd.gcs',
                'penilaian_medis_igd.kesadaran',
                'penilaian_medis_igd.td',
                'penilaian_medis_igd.nadi',
                'penilaian_medis_igd.rr',
                'penilaian_medis_igd.suhu',
                'penilaian_medis_igd.spo',
                'penilaian_medis_igd.bb',
                'penilaian_medis_igd.tb',
                'penilaian_medis_igd.kepala',
                'penilaian_medis_igd.mata',
                'penilaian_medis_igd.gigi',
                'penilaian_medis_igd.leher',
                'penilaian_medis_igd.thoraks',
                'penilaian_medis_igd.abdomen',
                'penilaian_medis_igd.ekstremitas',
                'penilaian_medis_igd.genital',
                'penilaian_medis_igd.ket_fisik',
                'penilaian_medis_igd.ket_lokalis',
                'penilaian_medis_igd.ekg',
                'penilaian_medis_igd.rad',
                'penilaian_medis_igd.lab',
                'penilaian_medis_igd.diagnosis',
                'penilaian_medis_igd.tata'
            )
            ->get();

        $riwayat['pemeriksaan_ralan'] = DB::table('pemeriksaan_ralan')
            ->join('pegawai', 'pemeriksaan_ralan.nip', '=', 'pegawai.nik')
            ->where('pemeriksaan_ralan.no_rawat', $no_rawat)
            ->select(
                'pemeriksaan_ralan.tgl_perawatan',
                'pemeriksaan_ralan.jam_rawat',
                'pemeriksaan_ralan.suhu_tubuh',
                'pemeriksaan_ralan.tensi',
                'pemeriksaan_ralan.nadi',
                'pemeriksaan_ralan.respirasi',
                'pemeriksaan_ralan.tinggi',
                'pemeriksaan_ralan.berat',
                'pemeriksaan_ralan.spo2',
                'pemeriksaan_ralan.gcs',
                'pemeriksaan_ralan.kesadaran',
                'pemeriksaan_ralan.keluhan',
                'pemeriksaan_ralan.pemeriksaan',
                'pemeriksaan_ralan.alergi',
                'pemeriksaan_ralan.lingkar_perut',
                'pemeriksaan_ralan.rtl',
                'pemeriksaan_ralan.penilaian',
                'pemeriksaan_ralan.instruksi',
                'pemeriksaan_ralan.evaluasi',
                'pemeriksaan_ralan.nip',
                'pegawai.nama',
                'pegawai.jbtn'
            )
            ->orderBy('pemeriksaan_ralan.tgl_perawatan')
            ->orderBy('pemeriksaan_ralan.jam_rawat')
            ->get();

        $riwayat['pemeriksaan_ranap'] = DB::table('pemeriksaan_ranap')
            ->join('pegawai', 'pemeriksaan_ranap.nip', '=', 'pegawai.nik')
            ->where('pemeriksaan_ranap.no_rawat', $no_rawat)
            ->select(
                'pemeriksaan_ranap.tgl_perawatan',
                'pemeriksaan_ranap.jam_rawat',
                'pemeriksaan_ranap.suhu_tubuh',
                'pemeriksaan_ranap.tensi',
                'pemeriksaan_ranap.nadi',
                'pemeriksaan_ranap.respirasi',
                'pemeriksaan_ranap.tinggi',
                'pemeriksaan_ranap.berat',
                'pemeriksaan_ranap.spo2',
                'pemeriksaan_ranap.gcs',
                'pemeriksaan_ranap.kesadaran',
                'pemeriksaan_ranap.keluhan',
                'pemeriksaan_ranap.pemeriksaan',
                'pemeriksaan_ranap.alergi',
                'pemeriksaan_ranap.rtl',
                'pemeriksaan_ranap.penilaian',
                'pemeriksaan_ranap.instruksi',
                'pemeriksaan_ranap.evaluasi',
                'pemeriksaan_ranap.nip',
                'pegawai.nama',
                'pegawai.jbtn'
            )
            ->orderBy('pemeriksaan_ranap.tgl_perawatan')
            ->orderBy('pemeriksaan_ranap.jam_rawat')
            ->get();

        $riwayat['tambahan_biaya'] = DB::table('tambahan_biaya')
            ->where('no_rawat', $no_rawat)
            ->select('nama_biaya', 'besar_biaya')
            ->get();

        $riwayat['potongan_biaya'] = DB::table('pengurangan_biaya')
            ->where('no_rawat', $no_rawat)
            ->selectRaw('nama_pengurangan, (-1 * besar_pengurangan) as besar_pengurangan')
            ->get();

        return $riwayat;
    }

    protected function calculateLegacyTotal(array $riwayat): float
    {
        $total = 0;

        foreach ($riwayat as $kategori => $items) {
            if ($kategori === 'ppn_obat') {
                $total += $items;
                continue;
            }

            if ($kategori === 'laboratorium_pkmb') {
                foreach ($items as $labGroup) {
                    foreach ($labGroup['pemeriksaan'] as $pemeriksaan) {
                        $total += $pemeriksaan->biaya ?? 0;
                        foreach ($pemeriksaan->detail as $detail) {
                            $total += $detail->biaya_item ?? 0;
                        }
                    }
                }
                continue;
            }

            if (is_iterable($items)) {
                foreach ($items as $item) {
                    $total += $item->biaya_rawat ?? 0;
                    $total += $item->ttl_biaya ?? 0;
                    $total += $item->biaya ?? 0;
                    $total += $item->total ?? 0;
                    $total += $item->besar_biaya ?? 0;
                    $total += $item->besar_pengurangan ?? 0;
                }
            }
        }

        return $total;
    }

    protected function mapLegacySections(array $riwayat): array
    {
        return [
            'rawat_jalan' => [
                'dokter' => $riwayat['rawat_jalan_dokter'] ?? [],
                'paramedis' => $riwayat['rawat_jalan_paramedis'] ?? [],
                'dokter_paramedis' => $riwayat['rawat_jalan_dokter_paramedis'] ?? [],
            ],
            'rawat_inap' => [
                'dokter' => $riwayat['rawat_inap_dokter'] ?? [],
                'paramedis' => $riwayat['rawat_inap_paramedis'] ?? [],
                'dokter_paramedis' => $riwayat['rawat_inap_dokter_paramedis'] ?? [],
                'kamar' => $riwayat['kamar_inap'] ?? [],
            ],
            'penunjang' => [
                'radiologi' => $riwayat['radiologi'] ?? [],
                'hasil_radiologi' => $riwayat['hasil_radiologi'] ?? [],
                'gambar_radiologi' => $riwayat['gambar_radiologi'] ?? [],
                'laboratorium_pkmb' => $riwayat['laboratorium_pkmb'] ?? [],
                'laboratorium_pa' => $riwayat['laboratorium_pa'] ?? [],
            ],
            'farmasi' => [
                'pemberian_obat' => $riwayat['pemberian_obat'] ?? [],
                'resep_pulang' => $riwayat['resep_pulang'] ?? [],
                'ppn_obat' => $riwayat['ppn_obat'] ?? 0,
            ],
            'asesmen' => [
                'triase_primer' => $riwayat['triase_primer'] ?? null,
                'triase_sekunder' => $riwayat['triase_sekunder'] ?? null,
                'asuhan_medis_igd' => $riwayat['asuhan_medis_igd'] ?? [],
                'pemeriksaan_ralan' => $riwayat['pemeriksaan_ralan'] ?? [],
                'pemeriksaan_ranap' => $riwayat['pemeriksaan_ranap'] ?? [],
            ],
            'administrasi' => [
                'tambahan_biaya' => $riwayat['tambahan_biaya'] ?? [],
                'potongan_biaya' => $riwayat['potongan_biaya'] ?? [],
            ],
        ];
    }

    protected function calculateStructuredTotals(array $sections): array
    {
        $breakdown = [
            'rawat_jalan' => 0,
            'rawat_inap' => 0,
            'penunjang' => 0,
            'farmasi' => 0,
            'administrasi' => 0,
        ];

        $breakdown['rawat_jalan'] += $this->sumItems($sections['rawat_jalan']['dokter'], 'biaya_rawat');
        $breakdown['rawat_jalan'] += $this->sumItems($sections['rawat_jalan']['paramedis'], 'biaya_rawat');
        $breakdown['rawat_jalan'] += $this->sumItems($sections['rawat_jalan']['dokter_paramedis'], 'biaya_rawat');

        $breakdown['rawat_inap'] += $this->sumItems($sections['rawat_inap']['dokter'], 'biaya_rawat');
        $breakdown['rawat_inap'] += $this->sumItems($sections['rawat_inap']['paramedis'], 'biaya_rawat');
        $breakdown['rawat_inap'] += $this->sumItems($sections['rawat_inap']['dokter_paramedis'], 'biaya_rawat');
        $breakdown['rawat_inap'] += $this->sumItems($sections['rawat_inap']['kamar'], 'ttl_biaya');

        $breakdown['penunjang'] += $this->sumItems($sections['penunjang']['radiologi'], 'biaya');
        $breakdown['penunjang'] += $this->sumLaboratoriumPkmb($sections['penunjang']['laboratorium_pkmb']);
        $breakdown['penunjang'] += $this->sumItems($sections['penunjang']['laboratorium_pa'], 'biaya');

        $breakdown['farmasi'] += $this->sumItems($sections['farmasi']['pemberian_obat'], 'total');
        $breakdown['farmasi'] += $this->sumItems($sections['farmasi']['resep_pulang'], 'total');
        $breakdown['farmasi'] += $sections['farmasi']['ppn_obat'] ?? 0;

        $breakdown['administrasi'] += $this->sumItems($sections['administrasi']['tambahan_biaya'], 'besar_biaya');
        $breakdown['administrasi'] += $this->sumItems($sections['administrasi']['potongan_biaya'], 'besar_pengurangan');

        $grandTotal = array_sum($breakdown);

        return [
            'grand_total' => $grandTotal,
            'breakdown' => $breakdown,
        ];
    }

    protected function sumItems(iterable $items, string $field): float
    {
        $total = 0;
        foreach ($items as $item) {
            $total += $item->{$field} ?? 0;
        }

        return $total;
    }

    protected function sumLaboratoriumPkmb(iterable $labGroups): float
    {
        $total = 0;

        foreach ($labGroups as $group) {
            foreach ($group['pemeriksaan'] as $pemeriksaan) {
                $total += $pemeriksaan->biaya ?? 0;
                foreach ($pemeriksaan->detail as $detail) {
                    $total += $detail->biaya_item ?? 0;
                }
            }
        }

        return $total;
    }

    protected function fetchTriaseSkala(string $no_rawat, int $skala): array
    {
        $table = match ($skala) {
            1 => ['master_triase_skala1', 'data_triase_igddetail_skala1', 'pengkajian_skala1'],
            2 => ['master_triase_skala2', 'data_triase_igddetail_skala2', 'pengkajian_skala2'],
            3 => ['master_triase_skala3', 'data_triase_igddetail_skala3', 'pengkajian_skala3'],
            4 => ['master_triase_skala4', 'data_triase_igddetail_skala4', 'pengkajian_skala4'],
            5 => ['master_triase_skala5', 'data_triase_igddetail_skala5', 'pengkajian_skala5'],
            default => null,
        };

        if (!$table) {
            return [];
        }

        [$masterTable, $detailTable, $fieldName] = $table;

        $groups = DB::table('master_triase_pemeriksaan')
            ->join($masterTable, 'master_triase_pemeriksaan.kode_pemeriksaan', '=', "{$masterTable}.kode_pemeriksaan")
            ->join($detailTable, "{$masterTable}.kode_skala{$skala}", '=', "{$detailTable}.kode_skala{$skala}")
            ->where("{$detailTable}.no_rawat", $no_rawat)
            ->select('master_triase_pemeriksaan.kode_pemeriksaan', 'master_triase_pemeriksaan.nama_pemeriksaan')
            ->groupBy('master_triase_pemeriksaan.kode_pemeriksaan', 'master_triase_pemeriksaan.nama_pemeriksaan')
            ->orderBy('master_triase_pemeriksaan.kode_pemeriksaan')
            ->get();

        $result = [];

        foreach ($groups as $group) {
            $details = DB::table($masterTable)
                ->join($detailTable, "{$masterTable}.kode_skala{$skala}", '=', "{$detailTable}.kode_skala{$skala}")
                ->where("{$masterTable}.kode_pemeriksaan", $group->kode_pemeriksaan)
                ->where("{$detailTable}.no_rawat", $no_rawat)
                ->select("{$masterTable}.{$fieldName} as deskripsi")
                ->orderBy("{$detailTable}.kode_skala{$skala}")
                ->get();

            $result[] = [
                'nama_pemeriksaan' => $group->nama_pemeriksaan,
                'details' => $details,
            ];
        }

        return $result;
    }
}

