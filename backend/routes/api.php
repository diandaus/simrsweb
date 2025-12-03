<?php

use App\Http\Controllers\PasienController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IGDController;
use App\Http\Controllers\PoliController;
use App\Http\Controllers\SOAPController;
use App\Http\Controllers\ObatController;
use App\Http\Controllers\ResepController;
use App\Http\Controllers\JadwalOperasiController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RanapController;
use App\Http\Controllers\LaboratoriumController;
use App\Http\Controllers\RadiologiController;
use App\Http\Controllers\TindakanController;
use App\Http\Controllers\HasilPenunjangController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::get('/pasien', [PasienController::class, 'index']);
    Route::get('/pasien/{no_rkm_medis}', [PasienController::class, 'show']);
    Route::post('/pasien', [PasienController::class, 'store']);
    Route::put('/pasien/{no_rkm_medis}', [PasienController::class, 'update']);

    Route::get('/riwayat-perawatan/pasien/{no_rkm_medis}', [PasienController::class, 'riwayatPerawatanPasien']);
    Route::get('/riwayat-perawatan/pasien/{no_rkm_medis}/export', [PasienController::class, 'exportRiwayatPerawatanPasien']);
    Route::get('/riwayat-perawatan/{no_rawat}', [PasienController::class, 'riwayatPerawatan'])->where('no_rawat', '.*');

    // IGD Routes
    Route::get('/igd', [IGDController::class, 'index']);

    // Poli Routes
    Route::get('/poli', [PoliController::class, 'index']);
    Route::get('/poli/rujukan-internal', [PoliController::class, 'rujukanInternal']);
    Route::get('/poli/list-poli', [PoliController::class, 'getPoli']);
    Route::get('/poli/list-dokter', [PoliController::class, 'getDokter']);
    Route::get('/poli/list-penjab', [PoliController::class, 'getPenjab']);

    // Poliklinik & Dokter Routes (for rujukan internal)
    Route::get('/poliklinik', [PoliController::class, 'getPoli']);
    Route::get('/dokter/bypoli/{kd_poli}', [PoliController::class, 'getDokterByPoli']);
    Route::post('/rujukan-internal', [PoliController::class, 'storeRujukanInternal']);

    // SOAP Routes
    Route::post('/soap', [SOAPController::class, 'store']);
    Route::put('/soap/update', [SOAPController::class, 'update']);
    Route::get('/soap/history/{no_rawat}', [SOAPController::class, 'history'])->where('no_rawat', '.*');
    Route::get('/soap/riwayat-soapie/{no_rkm_medis}', [SOAPController::class, 'riwayatSOAPIE']);
    Route::get('/soap/suggestions', [SOAPController::class, 'getHistory']); // Autocomplete history
    Route::get('/soap/vital-signs-history', [SOAPController::class, 'getVitalSignsHistory']); // Vital signs history
    Route::delete('/soap/{no_rawat}/{tgl_perawatan}/{jam_rawat}', [SOAPController::class, 'destroy'])->where(['no_rawat' => '.*', 'jam_rawat' => '.*']);

    // Obat Routes
    Route::get('/obat/debug', [ObatController::class, 'debug']);
    Route::get('/obat/search', [ObatController::class, 'search']);

    // Resep Routes
    Route::post('/resep', [ResepController::class, 'store']);
    Route::post('/resep/non-racikan', [ResepController::class, 'storeNonRacikan']); // Deprecated
    Route::post('/resep/racikan', [ResepController::class, 'storeRacikan']); // Deprecated
    Route::get('/resep/riwayat/{no_rkm_medis}', [ResepController::class, 'getRiwayatResep']);
    Route::get('/resep/{no_rawat}', [ResepController::class, 'getResep'])->where('no_rawat', '.*');

    // Jadwal Operasi Routes
    Route::get('/jadwal-operasi', [JadwalOperasiController::class, 'index']);
    Route::put('/operasi/update-tim', [JadwalOperasiController::class, 'updateTim']);
    Route::get('/operasi/laporan/{no_rawat}', [JadwalOperasiController::class, 'getLaporan'])->where('no_rawat', '.*');
    Route::post('/operasi/laporan', [JadwalOperasiController::class, 'storeLaporan']);
    Route::delete('/operasi/laporan/{no_rawat}/{tanggal}', [JadwalOperasiController::class, 'deleteLaporan'])->where(['no_rawat' => '.*', 'tanggal' => '.*']);
    Route::post('/operasi/anestesi', [JadwalOperasiController::class, 'storeAnestesi']);
    Route::get('/operasi/monitoring', [JadwalOperasiController::class, 'getMonitoring']);
    Route::post('/operasi/monitoring', [JadwalOperasiController::class, 'storeMonitoring']);
    Route::delete('/operasi/monitoring/{id}', [JadwalOperasiController::class, 'deleteMonitoring']);
    Route::get('/operasi/dokter', [JadwalOperasiController::class, 'getDokter']);
    Route::get('/operasi/petugas', [JadwalOperasiController::class, 'getPetugas']);

    // User Management Routes
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/search-pegawai', [UserController::class, 'searchPegawai']);
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    Route::post('/users/upload-avatar', [UserController::class, 'uploadAvatar']);

    // Rawat Inap Routes
    Route::get('/ranap', [RanapController::class, 'index']);
    Route::get('/ranap/bangsal', [RanapController::class, 'getBangsal']);

    // Laboratorium Routes
    Route::get('/laboratorium/pemeriksaan-pk', [LaboratoriumController::class, 'getPemeriksaanPK']);
    Route::get('/laboratorium/pemeriksaan-pa', [LaboratoriumController::class, 'getPemeriksaanPA']);
    Route::get('/laboratorium/template/{kd_jenis_prw}', [LaboratoriumController::class, 'getTemplatePK']);
    Route::post('/laboratorium/permintaan', [LaboratoriumController::class, 'storePermintaan']);
    Route::get('/laboratorium/riwayat/{no_rawat}', [LaboratoriumController::class, 'getRiwayat'])->where('no_rawat', '.*');
    Route::get('/laboratorium/riwayat-pa/{no_rawat}', [LaboratoriumController::class, 'getRiwayatPA'])->where('no_rawat', '.*');

    // Radiologi Routes
    Route::get('/radiologi/pemeriksaan', [RadiologiController::class, 'getPemeriksaan']);
    Route::post('/radiologi/permintaan', [RadiologiController::class, 'storePermintaan']);
    Route::get('/radiologi/riwayat/{no_rawat}', [RadiologiController::class, 'getRiwayat'])->where('no_rawat', '.*');

    // Tindakan Routes
    Route::get('/tindakan/jenis-perawatan', [TindakanController::class, 'getJenisPerawatan']);
    Route::post('/tindakan/simpan', [TindakanController::class, 'storeTindakan']);
    Route::get('/tindakan/riwayat/{no_rawat}', [TindakanController::class, 'getRiwayat'])->where('no_rawat', '.*');

    // Hasil Penunjang Routes (EKG/USG)
    Route::post('/hasil-penunjang/simpan', [HasilPenunjangController::class, 'store']);
    Route::get('/hasil-penunjang/riwayat/{no_rawat}', [HasilPenunjangController::class, 'getRiwayat'])->where('no_rawat', '.*');
});