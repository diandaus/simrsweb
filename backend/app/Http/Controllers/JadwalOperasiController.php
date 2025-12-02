<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JadwalOperasiController extends Controller
{
    /**
     * Get jadwal operasi dengan filter
     */
    public function index(Request $request)
    {
        try {
            $status = $request->input('status', 'Menunggu');
            $tanggal_dari = $request->input('tanggal_dari');
            $tanggal_sampai = $request->input('tanggal_sampai');
            $search = $request->input('search', '');

            // Build query
            $query = DB::table('booking_operasi')
                ->join('reg_periksa', 'booking_operasi.no_rawat', '=', 'reg_periksa.no_rawat')
                ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
                ->join('paket_operasi', 'booking_operasi.kode_paket', '=', 'paket_operasi.kode_paket')
                ->join('dokter', 'booking_operasi.kd_dokter', '=', 'dokter.kd_dokter')
                ->join('poliklinik', 'reg_periksa.kd_poli', '=', 'poliklinik.kd_poli')
                ->join('ruang_ok', 'booking_operasi.kd_ruang_ok', '=', 'ruang_ok.kd_ruang_ok')
                ->select(
                    'booking_operasi.no_rawat',
                    'reg_periksa.no_rkm_medis',
                    'pasien.nm_pasien',
                    'booking_operasi.tanggal',
                    'booking_operasi.jam_mulai',
                    'booking_operasi.jam_selesai',
                    'booking_operasi.status',
                    'booking_operasi.kd_dokter',
                    'dokter.nm_dokter',
                    'booking_operasi.kode_paket',
                    'paket_operasi.nm_perawatan',
                    DB::raw("concat(reg_periksa.umurdaftar,' ',reg_periksa.sttsumur) as umur"),
                    'pasien.jk',
                    'poliklinik.nm_poli',
                    'booking_operasi.kd_ruang_ok',
                    'ruang_ok.nm_ruang_ok'
                );

            // Filter berdasarkan status atau tanggal
            if ($status === 'Menunggu') {
                $query->where('booking_operasi.status', 'Menunggu');
            } elseif ($status === 'Proses Operasi') {
                $query->where('booking_operasi.status', 'Proses Operasi');
            } elseif ($status === 'Selesai' && $tanggal_dari && $tanggal_sampai) {
                $query->where('booking_operasi.status', 'Selesai')
                      ->whereBetween('booking_operasi.tanggal', [$tanggal_dari, $tanggal_sampai]);
            } elseif ($status === 'Rentang Tanggal' && $tanggal_dari && $tanggal_sampai) {
                $query->whereBetween('booking_operasi.tanggal', [$tanggal_dari, $tanggal_sampai]);
            }

            // Search
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('booking_operasi.no_rawat', 'like', "%$search%")
                      ->orWhere('reg_periksa.no_rkm_medis', 'like', "%$search%")
                      ->orWhere('pasien.nm_pasien', 'like', "%$search%")
                      ->orWhere('booking_operasi.status', 'like', "%$search%")
                      ->orWhere('dokter.nm_dokter', 'like', "%$search%")
                      ->orWhere('paket_operasi.nm_perawatan', 'like', "%$search%")
                      ->orWhere('ruang_ok.nm_ruang_ok', 'like', "%$search%");
                });
            }

            $results = $query->orderBy('booking_operasi.tanggal')
                           ->orderBy('booking_operasi.jam_mulai')
                           ->get();

            // Enhance data with additional info
            foreach ($results as $row) {
                // Format jam_mulai dan jam_selesai untuk memastikan format HH:MM:SS
                if ($row->jam_mulai) {
                    // Jika sudah format time, biarkan. Jika null atau kosong, set default
                    $row->jam_mulai = substr($row->jam_mulai, 0, 8); // Ambil HH:MM:SS saja
                }
                if ($row->jam_selesai) {
                    $row->jam_selesai = substr($row->jam_selesai, 0, 8); // Ambil HH:MM:SS saja
                }

                // Get kamar/bangsal
                $kamar = DB::table('bangsal')
                    ->join('kamar', 'bangsal.kd_bangsal', '=', 'kamar.kd_bangsal')
                    ->join('kamar_inap', 'kamar_inap.kd_kamar', '=', 'kamar.kd_kamar')
                    ->where('kamar_inap.no_rawat', $row->no_rawat)
                    ->orderBy('kamar_inap.tgl_masuk', 'desc')
                    ->limit(1)
                    ->value('nm_bangsal');

                if ($kamar) {
                    $row->kamar = $kamar;
                    $row->order = 'Ranap';
                } else {
                    $row->kamar = $row->nm_poli;
                    $row->order = 'Ralan';
                }

                // Get diagnosa
                $diagnosa = DB::table('diagnosa_pasien')
                    ->join('penyakit', 'diagnosa_pasien.kd_penyakit', '=', 'penyakit.kd_penyakit')
                    ->where('diagnosa_pasien.no_rawat', $row->no_rawat)
                    ->limit(1)
                    ->selectRaw("concat(diagnosa_pasien.kd_penyakit,' ',penyakit.nm_penyakit) as diagnosa")
                    ->value('diagnosa');

                $row->diagnosa = $diagnosa ?? '-';
            }

            return response()->json([
                'success' => true,
                'data' => $results,
                'total' => $results->count()
            ]);
        } catch (\Exception $e) {
            \Log::error('Get jadwal operasi error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data jadwal operasi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update tim operasi (tanggal, jam, status)
     */
    public function updateTim(Request $request)
    {
        $request->validate([
            'no_rawat' => 'required',
            'tanggal' => 'required|date',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'status' => 'required|in:Proses Operasi,Selesai'
        ]);

        try {
            // Get existing booking data
            $booking = DB::table('booking_operasi')
                ->where('no_rawat', $request->no_rawat)
                ->first();

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data booking operasi tidak ditemukan'
                ], 404);
            }

            // Cek jadwal bentrok jika bukan status yang sama
            if ($request->kd_ruang_ok && $request->kd_ruang_ok !== $booking->kd_ruang_ok) {
                $conflict = DB::table('booking_operasi')
                    ->where('tanggal', $request->tanggal)
                    ->where('kd_ruang_ok', $request->kd_ruang_ok)
                    ->where('no_rawat', '!=', $request->no_rawat)
                    ->where(function($query) use ($request) {
                        $query->whereBetween('jam_mulai', [$request->jam_mulai, $request->jam_selesai])
                              ->orWhereBetween('jam_selesai', [$request->jam_mulai, $request->jam_selesai])
                              ->orWhere(function($q) use ($request) {
                                  $q->where('jam_mulai', '<=', $request->jam_mulai)
                                    ->where('jam_selesai', '>=', $request->jam_selesai);
                              });
                    })
                    ->count();

                if ($conflict > 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Jadwal bentrok dengan jam mulai operasi yang lain!'
                    ], 422);
                }
            }

            // Log data yang akan diupdate
            \Log::info('Update tim operasi', [
                'no_rawat' => $request->no_rawat,
                'kode_paket' => $booking->kode_paket,
                'status' => $request->status
            ]);

            // Update booking_operasi
            $updated = DB::table('booking_operasi')
                ->where('no_rawat', $request->no_rawat)
                ->where('kode_paket', $booking->kode_paket)
                ->update([
                    'tanggal' => $request->tanggal,
                    'jam_mulai' => $request->jam_mulai,
                    'jam_selesai' => $request->jam_selesai,
                    'status' => $request->status
                ]);

            \Log::info('Update result', ['updated' => $updated]);

            // Return success even if no rows changed (data might be the same)
            return response()->json([
                'success' => true,
                'message' => 'Data tim operasi berhasil diupdate'
            ]);
        } catch (\Exception $e) {
            \Log::error('Update tim operasi error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate data tim operasi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get list dokter
     */
    public function getDokter()
    {
        try {
            $dokter = DB::table('dokter')
                ->select('kd_dokter', 'nm_dokter')
                ->where('status', '1')
                ->orderBy('nm_dokter')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $dokter
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data dokter: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get list petugas (asisten)
     */
    public function getPetugas()
    {
        try {
            $petugas = DB::table('petugas')
                ->select('nip', 'nama')
                ->where('status', '1')
                ->orderBy('nama')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $petugas
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data petugas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get monitoring anestesi list
     */
    public function getMonitoring(Request $request)
    {
        try {
            $noRawat = $request->input('no_rawat');
            $tanggalOperasi = $request->input('tanggal_operasi');

            $monitoring = DB::table('monitoring_anestesi_sedasi')
                ->where('no_rawat', $noRawat)
                ->where('tanggal_operasi', $tanggalOperasi)
                ->orderBy('waktu_monitoring', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $monitoring
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data monitoring: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store monitoring anestesi
     */
    public function storeMonitoring(Request $request)
    {
        $request->validate([
            'no_rawat' => 'required',
            'tanggal_operasi' => 'required',
            'waktu_monitoring' => 'required'
        ]);

        try {
            $data = [
                'no_rawat' => $request->no_rawat,
                'tanggal_operasi' => $request->tanggal_operasi,
                'waktu_monitoring' => $request->waktu_monitoring,
                'menit_ke' => $request->menit_ke,
                'td_systole' => $request->td_systole,
                'td_diastole' => $request->td_diastole,
                'heart_rate' => $request->heart_rate,
                'respiratory_rate' => $request->respiratory_rate,
                'spo2' => $request->spo2,
                'etco2' => $request->etco2,
                'temperature' => $request->temperature,
                'map' => $request->map,
                'cvp' => $request->cvp,
                'obat_diberikan' => $request->obat_diberikan,
                'dosis_obat' => $request->dosis_obat,
                'cairan_masuk' => $request->cairan_masuk,
                'urine_output' => $request->urine_output,
                'perdarahan' => $request->perdarahan,
                'catatan' => $request->catatan,
                'nip_petugas' => $request->nip_petugas
            ];

            DB::table('monitoring_anestesi_sedasi')->insert($data);

            return response()->json([
                'success' => true,
                'message' => 'Data monitoring berhasil disimpan'
            ]);
        } catch (\Exception $e) {
            \Log::error('Store monitoring error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data monitoring: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete monitoring anestesi
     */
    public function deleteMonitoring($id)
    {
        try {
            DB::table('monitoring_anestesi_sedasi')
                ->where('id', $id)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data monitoring berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data monitoring: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store laporan anestesi
     */
    public function storeAnestesi(Request $request)
    {
        $request->validate([
            'no_rawat' => 'required',
            'tanggal' => 'required',
            'kd_dokter_bedah' => 'required',
            'kd_dokter_anestesi' => 'required'
        ]);

        try {
            // Cek apakah sudah ada laporan untuk no_rawat dan tanggal ini
            $existing = DB::table('catatan_anestesi_sedasi')
                ->where('no_rawat', $request->no_rawat)
                ->where('tanggal', $request->tanggal)
                ->first();

            $data = [
                'no_rawat' => $request->no_rawat,
                'tanggal' => $request->tanggal,
                'kd_dokter_bedah' => $request->kd_dokter_bedah,
                'kd_dokter_anestesi' => $request->kd_dokter_anestesi,
                'diagnosa_pre_bedah' => $request->diagnosa_pre_bedah ?? '',
                'tindakan_jenis_pembedahan' => $request->tindakan_jenis_pembedahan ?? '',
                'diagnosa_pasca_bedah' => $request->diagnosa_pasca_bedah ?? '',
                'pre_induksi_jam' => $request->pre_induksi_jam,
                'pre_induksi_kesadaran' => $request->pre_induksi_kesadaran,
                'pre_induksi_td' => $request->pre_induksi_td,
                'pre_induksi_nadi' => $request->pre_induksi_nadi,
                'pre_induksi_rr' => $request->pre_induksi_rr,
                'pre_induksi_suhu' => $request->pre_induksi_suhu,
                'pre_induksi_o2' => $request->pre_induksi_o2,
                'pre_induksi_tb' => $request->pre_induksi_tb,
                'pre_induksi_bb' => $request->pre_induksi_bb,
                'pre_induksi_rhesus' => $request->pre_induksi_rhesus,
                'pre_induksi_hb' => $request->pre_induksi_hb,
                'pre_induksi_ht' => $request->pre_induksi_ht,
                'pre_induksi_leko' => $request->pre_induksi_leko,
                'pre_induksi_trombo' => $request->pre_induksi_trombo,
                'pre_induksi_btct' => $request->pre_induksi_btct,
                'pre_induksi_gds' => $request->pre_induksi_gds,
                'pre_induksi_lainlain' => $request->pre_induksi_lainlain,
                'monitoring_ekg' => $request->monitoring_ekg ?? 'Tidak',
                'monitoring_ekg_keterangan' => $request->monitoring_ekg_keterangan,
                'monitoring_arteri' => $request->monitoring_arteri ?? 'Tidak',
                'monitoring_arteri_keterangan' => $request->monitoring_arteri_keterangan,
                'monitoring_cvp' => $request->monitoring_cvp ?? 'Tidak',
                'monitoring_cvp_keterangan' => $request->monitoring_cvp_keterangan,
                'monitoring_etco' => $request->monitoring_etco ?? 'Tidak',
                'monitoring_stetoskop' => $request->monitoring_stetoskop ?? 'Tidak',
                'monitoring_nibp' => $request->monitoring_nibp ?? 'Tidak',
                'monitoring_ngt' => $request->monitoring_ngt ?? 'Tidak',
                'monitoring_bis' => $request->monitoring_bis ?? 'Tidak',
                'monitoring_cath_a_pulmo' => $request->monitoring_cath_a_pulmo ?? 'Tidak',
                'monitoring_spo2' => $request->monitoring_spo2 ?? 'Tidak',
                'monitoring_kateter' => $request->monitoring_kateter ?? 'Tidak',
                'monitoring_temp' => $request->monitoring_temp ?? 'Tidak',
                'monitoring_lainlain' => $request->monitoring_lainlain,
                'status_fisik_asa' => $request->status_fisik_asa,
                'status_fisik_alergi' => $request->status_fisik_alergi ?? 'Tidak',
                'status_fisik_alergi_keterangan' => $request->status_fisik_alergi_keterangan,
                'status_fisik_penyulit_sedasi' => $request->status_fisik_penyulit_sedasi,
                'perencanaan_lanjut' => $request->perencanaan_lanjut ?? 'Tidak',
                'perencanaan_lanjut_sedasi' => $request->perencanaan_lanjut_sedasi ?? 'Tidak',
                'perencanaan_lanjut_sedasi_keterangan' => $request->perencanaan_lanjut_sedasi_keterangan,
                'perencanaan_lanjut_spinal' => $request->perencanaan_lanjut_spinal ?? 'Tidak',
                'perencanaan_lanjut_anestesi_umum' => $request->perencanaan_lanjut_anestesi_umum ?? 'Tidak',
                'perencanaan_lanjut_anestesi_umum_keterangan' => $request->perencanaan_lanjut_anestesi_umum_keterangan,
                'perencanaan_lanjut_blok_perifer' => $request->perencanaan_lanjut_blok_perifer ?? 'Tidak',
                'perencanaan_lanjut_blok_perifer_keterangan' => $request->perencanaan_lanjut_blok_perifer_keterangan,
                'perencanaan_lanjut_epidural' => $request->perencanaan_lanjut_epidural ?? 'Tidak',
                'perencanaan_batal' => $request->perencanaan_batal ?? 'Tidak',
                'perencanaan_batal_alasan' => $request->perencanaan_batal_alasan,
                'nip_perawat_ok' => $request->nip_perawat_ok,
                'nip_perawat_anestesi' => $request->nip_perawat_anestesi
            ];

            if ($existing) {
                // Update existing
                DB::table('catatan_anestesi_sedasi')
                    ->where('no_rawat', $request->no_rawat)
                    ->where('tanggal', $request->tanggal)
                    ->update($data);
                $message = 'Laporan anestesi berhasil diupdate';
            } else {
                // Insert new
                DB::table('catatan_anestesi_sedasi')->insert($data);
                $message = 'Laporan anestesi berhasil disimpan';
            }

            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            \Log::error('Store laporan anestesi error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan laporan anestesi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get laporan operasi by no_rawat
     */
    public function getLaporan($no_rawat)
    {
        try {
            $laporan = DB::table('laporan_bedah')
                ->leftJoin('dokter as db', 'laporan_bedah.kd_dokter_bedah', '=', 'db.kd_dokter')
                ->leftJoin('dokter as da', 'laporan_bedah.kd_dokter_anestesi', '=', 'da.kd_dokter')
                ->leftJoin('petugas', 'laporan_bedah.nip_perawat_ok', '=', 'petugas.nip')
                ->where('laporan_bedah.no_rawat', $no_rawat)
                ->select(
                    'laporan_bedah.*',
                    'db.nm_dokter as nm_dokter_bedah',
                    'da.nm_dokter as nm_dokter_anestesi',
                    'petugas.nama as nm_perawat_ok'
                )
                ->orderBy('laporan_bedah.tanggal', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $laporan
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil laporan operasi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store laporan operasi
     */
    public function storeLaporan(Request $request)
    {
        $request->validate([
            'no_rawat' => 'required',
            'tanggal' => 'required',
            'jenis' => 'required',
            'tindakan' => 'required'
        ]);

        try {
            // Cek apakah sudah ada laporan untuk no_rawat dan tanggal ini
            $existing = DB::table('laporan_bedah')
                ->where('no_rawat', $request->no_rawat)
                ->where('tanggal', $request->tanggal)
                ->first();

            $data = [
                'no_rawat' => $request->no_rawat,
                'tanggal' => $request->tanggal,
                'jenis' => $request->jenis,
                'tindakan' => $request->tindakan,
                'kd_dokter_bedah' => $request->kd_dokter_bedah ?? '-',
                'kd_dokter_anestesi' => $request->kd_dokter_anestesi ?? '-',
                'kategori' => $request->kategori ?? '-',
                'diagnosis_pre' => $request->diagnosis_pre,
                'diagnosis_post' => $request->diagnosis_post,
                'jaringan' => $request->jaringan,
                'pemeriksaan_pa' => $request->pemeriksaan_pa ?? 'Tidak',
                'ket_pa' => $request->ket_pa,
                'output_cairan' => $request->output_cairan ?? 'Darah',
                'ket_output' => $request->ket_output,
                'laporan_operasi' => $request->laporan_operasi,
                'selesaioperasi' => $request->selesaioperasi,
                'nip_perawat_ok' => $request->nip_perawat_ok
            ];

            if ($existing) {
                // Update existing
                DB::table('laporan_bedah')
                    ->where('no_rawat', $request->no_rawat)
                    ->where('tanggal', $request->tanggal)
                    ->update($data);

                $message = 'Laporan operasi berhasil diupdate';
            } else {
                // Insert new
                DB::table('laporan_bedah')->insert($data);
                $message = 'Laporan operasi berhasil disimpan';
            }

            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            \Log::error('Store laporan operasi error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan laporan operasi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete laporan operasi
     */
    public function deleteLaporan($no_rawat, $tanggal)
    {
        try {
            $deleted = DB::table('laporan_bedah')
                ->where('no_rawat', $no_rawat)
                ->where('tanggal', $tanggal)
                ->delete();

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Laporan operasi berhasil dihapus'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Laporan operasi tidak ditemukan'
                ], 404);
            }
        } catch (\Exception $e) {
            \Log::error('Delete laporan operasi error', [
                'message' => $e->getMessage(),
                'no_rawat' => $no_rawat,
                'tanggal' => $tanggal
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus laporan operasi: ' . $e->getMessage()
            ], 500);
        }
    }
}
