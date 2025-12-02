<?php

namespace App\Http\Controllers;

use App\Models\Pasien;
use App\Services\RiwayatPerawatanService;
use Illuminate\Http\Request;

class PasienController extends Controller
{
    public function __construct(
        protected RiwayatPerawatanService $riwayatService
    ) {
    }

    public function index(Request $request)
    {
        $query = Pasien::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('no_rkm_medis', 'like', "%{$search}%")
                  ->orWhere('nm_pasien', 'like', "%{$search}%")
                  ->orWhere('no_ktp', 'like', "%{$search}%")
                  ->orWhere('no_peserta', 'like', "%{$search}%");
            });
        }

        $perPage = $request->get('per_page', 50);
        $pasien = $query->orderBy('tgl_daftar', 'desc')
                       ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $pasien->items(),
            'meta' => [
                'current_page' => $pasien->currentPage(),
                'last_page' => $pasien->lastPage(),
                'per_page' => $pasien->perPage(),
                'total' => $pasien->total()
            ]
        ]);
    }

    public function show($no_rkm_medis)
    {
        $pasien = Pasien::with(['regPeriksa' => function($query) {
            $query->orderBy('tgl_registrasi', 'desc')
                  ->orderBy('jam_reg', 'desc')
                  ->limit(10);
        }])->find($no_rkm_medis);

        if (!$pasien) {
            return response()->json([
                'success' => false,
                'message' => 'Pasien tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $pasien
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'no_rkm_medis' => 'required|unique:pasien',
            'nm_pasien' => 'required|string|max:40',
            'no_ktp' => 'nullable|string|max:20',
            'jk' => 'required|in:L,P',
            'tmp_lahir' => 'nullable|string|max:15',
            'tgl_lahir' => 'required|date',
            'alamat' => 'nullable|string|max:200',
            'no_tlp' => 'nullable|string|max:40',
            'agama' => 'nullable|string|max:12',
            'pekerjaan' => 'nullable|string|max:60',
            'stts_nikah' => 'nullable|in:BELUM MENIKAH,MENIKAH,JANDA,DUDHA,JOMBLO'
        ]);

        $validated['tgl_daftar'] = now()->format('Y-m-d');

        $pasien = Pasien::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Pasien berhasil ditambahkan',
            'data' => $pasien
        ], 201);
    }

    public function update(Request $request, $no_rkm_medis)
    {
        $pasien = Pasien::find($no_rkm_medis);

        if (!$pasien) {
            return response()->json([
                'success' => false,
                'message' => 'Pasien tidak ditemukan'
            ], 404);
        }

        $validated = $request->validate([
            'nm_pasien' => 'sometimes|required|string|max:40',
            'no_ktp' => 'nullable|string|max:20',
            'jk' => 'sometimes|required|in:L,P',
            'tmp_lahir' => 'nullable|string|max:15',
            'tgl_lahir' => 'sometimes|required|date',
            'alamat' => 'nullable|string|max:200',
            'no_tlp' => 'nullable|string|max:40',
            'agama' => 'nullable|string|max:12',
            'pekerjaan' => 'nullable|string|max:60'
        ]);

        $pasien->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data pasien berhasil diupdate',
            'data' => $pasien
        ]);
    }

    public function riwayatPerawatan($no_rawat)
    {
        $result = $this->riwayatService->getLegacyDetail($no_rawat);

        return response()->json([
            'success' => true,
            'data' => $result['riwayat'],
            'total_biaya' => $result['total_biaya'],
        ]);
    }

    public function riwayatPerawatanPasien($no_rkm_medis)
    {
        $timeline = $this->riwayatService->getTimelineByPasien($no_rkm_medis);

        return response()->json([
            'success' => true,
            'data' => $timeline,
        ]);
    }

    public function exportRiwayatPerawatanPasien(Request $request, $no_rkm_medis)
    {
        $format = $request->get('format', 'json');
        $timeline = $this->riwayatService->getTimelineByPasien($no_rkm_medis);

        if ($format !== 'json') {
            return response()->json([
                'success' => false,
                'message' => 'Format export belum didukung',
            ], 400);
        }

        $filename = sprintf('riwayat_perawatan_%s.json', $no_rkm_medis);
        $payload = [
            'generated_at' => now()->toIso8601String(),
            'no_rkm_medis' => $no_rkm_medis,
            'data' => $timeline,
        ];

        return response()->streamDownload(
            function () use ($payload) {
                echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            },
            $filename,
            ['Content-Type' => 'application/json']
        );
    }
}