<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ObatController extends Controller
{
    /**
     * Debug endpoint to check database structure
     */
    public function debug(Request $request)
    {
        try {
            $kd_bangsal = $request->input('kd_bangsal', 'APK');

            // Check gudangbarang
            $gudangCount = DB::table('gudangbarang')
                ->where('kd_bangsal', $kd_bangsal)
                ->count();

            $sampleGudang = DB::table('gudangbarang')
                ->where('kd_bangsal', $kd_bangsal)
                ->limit(5)
                ->get();

            // Check databarang
            $databarangCount = DB::table('databarang')
                ->where('status', '1')
                ->count();

            $sampleDatabarang = DB::table('databarang')
                ->where('status', '1')
                ->limit(5)
                ->get();

            return response()->json([
                'success' => true,
                'debug' => [
                    'gudangbarang_count' => $gudangCount,
                    'gudangbarang_sample' => $sampleGudang,
                    'databarang_count' => $databarangCount,
                    'databarang_sample' => $sampleDatabarang,
                    'kd_bangsal' => $kd_bangsal
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search obat by name for resep
     */
    public function search(Request $request)
    {
        $query = $request->input('q', '');
        $kd_bangsal = $request->input('kd_bangsal', 'APK'); // Default farmasi (Apotek)
        $markup = $request->input('markup', 0); // Markup percentage for harga
        $stok_kosong = $request->input('stok_kosong', 'no'); // yes or no - show zero stock items

        if (strlen($query) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'Query minimal 2 karakter'
            ], 400);
        }

        try {
            // Log query for debugging
            \Log::info('Search Obat', [
                'query' => $query,
                'kd_bangsal' => $kd_bangsal,
                'markup' => $markup,
                'stok_kosong' => $stok_kosong
            ]);

            // Query that handles BOTH batch and non-batch items
            // Using UNION to combine both types
            $obat = DB::select("
                SELECT
                    d.kode_brng,
                    d.nama_brng,
                    j.nama as jenis_obat,
                    d.kode_sat,
                    (d.h_beli + (d.h_beli * ?)) as harga,
                    d.letak_barang,
                    i.nama_industri,
                    d.h_beli,
                    d.kapasitas,
                    COALESCE(batch_stok.stok, 0) + COALESCE(non_batch_stok.stok, 0) as stok
                FROM databarang d
                INNER JOIN jenis j ON d.kdjns = j.kdjns
                INNER JOIN industrifarmasi i ON i.kode_industri = d.kode_industri
                LEFT JOIN (
                    SELECT kode_brng, SUM(stok) as stok
                    FROM gudangbarang
                    WHERE kd_bangsal = ?
                      AND no_batch <> ''
                      AND no_faktur <> ''
                    GROUP BY kode_brng
                ) batch_stok ON d.kode_brng = batch_stok.kode_brng
                LEFT JOIN (
                    SELECT kode_brng, stok
                    FROM gudangbarang
                    WHERE kd_bangsal = ?
                      AND no_batch = ''
                      AND no_faktur = ''
                ) non_batch_stok ON d.kode_brng = non_batch_stok.kode_brng
                WHERE d.status = '1'
                  AND (batch_stok.kode_brng IS NOT NULL OR non_batch_stok.kode_brng IS NOT NULL)
                  " . ($stok_kosong === 'no' ? " AND (COALESCE(batch_stok.stok, 0) + COALESCE(non_batch_stok.stok, 0)) > 0 " : "") . "
                  AND (
                    d.kode_brng LIKE ?
                    OR d.nama_brng LIKE ?
                    OR j.nama LIKE ?
                    OR d.letak_barang LIKE ?
                  )
                ORDER BY d.nama_brng
                LIMIT 20
            ", [
                $markup,
                $kd_bangsal,
                $kd_bangsal,
                '%' . $query . '%',
                '%' . $query . '%',
                '%' . $query . '%',
                '%' . $query . '%'
            ]);

            \Log::info('Search result', [
                'count' => count($obat),
                'first_item' => !empty($obat) ? $obat[0] : null
            ]);

            return response()->json([
                'success' => true,
                'data' => $obat
            ]);
        } catch (\Exception $e) {
            \Log::error('Search obat error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mencari obat: ' . $e->getMessage()
            ], 500);
        }
    }
}
