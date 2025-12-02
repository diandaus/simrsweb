<?php

namespace App\Http\Controllers;

use App\Models\Pasien;
use App\Models\RegPeriksa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            // Total pasien
            $totalPasien = Pasien::count();

            // Pasien baru bulan ini
            $pasienBulanIni = Pasien::whereMonth('tgl_daftar', date('m'))
                ->whereYear('tgl_daftar', date('Y'))
                ->count();

            // Kunjungan hari ini
            $kunjunganHariIni = RegPeriksa::whereDate('tgl_registrasi', date('Y-m-d'))
                ->count();

            // Kunjungan bulan ini
            $kunjunganBulanIni = RegPeriksa::whereMonth('tgl_registrasi', date('m'))
                ->whereYear('tgl_registrasi', date('Y'))
                ->count();

            // Kunjungan per status
            $kunjunganPerStatus = RegPeriksa::select('stts', DB::raw('count(*) as total'))
                ->whereMonth('tgl_registrasi', date('m'))
                ->whereYear('tgl_registrasi', date('Y'))
                ->groupBy('stts')
                ->get();

            // Kunjungan per status lanjut (Ralan/Ranap)
            $kunjunganPerStatusLanjut = RegPeriksa::select('status_lanjut', DB::raw('count(*) as total'))
                ->whereMonth('tgl_registrasi', date('m'))
                ->whereYear('tgl_registrasi', date('Y'))
                ->groupBy('status_lanjut')
                ->get();

            // Kunjungan 7 hari terakhir
            $kunjungan7Hari = RegPeriksa::select(
                    DB::raw('DATE(tgl_registrasi) as tanggal'),
                    DB::raw('count(*) as total')
                )
                ->where('tgl_registrasi', '>=', date('Y-m-d', strtotime('-7 days')))
                ->groupBy('tanggal')
                ->orderBy('tanggal', 'asc')
                ->get();

            // Top 5 poli dengan kunjungan terbanyak bulan ini
            $topPoli = RegPeriksa::select('kd_poli', DB::raw('count(*) as total'))
                ->whereMonth('tgl_registrasi', date('m'))
                ->whereYear('tgl_registrasi', date('Y'))
                ->groupBy('kd_poli')
                ->orderBy('total', 'desc')
                ->limit(5)
                ->get();

            // Pasien baru 6 bulan terakhir
            $pasienPerBulan = Pasien::select(
                    DB::raw('YEAR(tgl_daftar) as tahun'),
                    DB::raw('MONTH(tgl_daftar) as bulan'),
                    DB::raw('count(*) as total')
                )
                ->where('tgl_daftar', '>=', date('Y-m-d', strtotime('-6 months')))
                ->groupBy('tahun', 'bulan')
                ->orderBy('tahun', 'asc')
                ->orderBy('bulan', 'asc')
                ->get();

            // Distribusi jenis kelamin
            $distribusiJK = Pasien::select('jk', DB::raw('count(*) as total'))
                ->groupBy('jk')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'statistik' => [
                        'total_pasien' => $totalPasien,
                        'pasien_bulan_ini' => $pasienBulanIni,
                        'kunjungan_hari_ini' => $kunjunganHariIni,
                        'kunjungan_bulan_ini' => $kunjunganBulanIni,
                    ],
                    'kunjungan_per_status' => $kunjunganPerStatus,
                    'kunjungan_per_status_lanjut' => $kunjunganPerStatusLanjut,
                    'kunjungan_7_hari' => $kunjungan7Hari,
                    'top_poli' => $topPoli,
                    'pasien_per_bulan' => $pasienPerBulan,
                    'distribusi_jk' => $distribusiJK,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data dashboard',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
