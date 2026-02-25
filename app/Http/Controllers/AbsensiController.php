<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\FaceClient;
use App\Models\Siswa;
use App\Models\Absensi;

class AbsensiController extends Controller
{
    public function index()
    {
        return view('admin.pages.absensi.index');
    }

    public function recognize(Request $request, FaceClient $faceApi)
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:4096'],
        ]);

        $path = $request->file('image')->store('tmp', 'local');
        $fullPath = Storage::disk('local')->path($path);

        try {
            $res = $faceApi->recognize($fullPath);
        } finally {
            Storage::disk('local')->delete($path);
        }

        // 1) HTTP error (timeout/500/dll)
        if (!$res->successful()) {
            return response()->json([
                'ok' => false,
                'message' => 'FACE_API_ERROR',
                'detail' => $res->json('detail') ?? $res->body(),
            ], 500);
        }

        // 2) API balas ok=false walau status 200
        if ($res->json('ok') !== true) {
            return response()->json([
                'ok' => false,
                'message' => $res->json('message') ?? 'FACE_API_ERROR',
                'detail' => $res->json('detail') ?? null,
            ], 200);
        }

        // 3) pastikan ada hasil
        $nis = $res->json('results.0.id');
        $percent = $res->json('results.0.percent');

        if ($nis === null || $percent === null) {
            return response()->json([
                'ok' => false,
                'message' => 'FACE_NOT_FOUND',
                'detail' => 'Wajah tidak terdeteksi atau tidak ada match.',
            ], 200);
        }

        $percent = (float) $percent;

        // 4) syarat minimal 80%
        if ($percent < 70) {
            return response()->json([
                'ok' => false,
                'message' => 'FACE_NOT_RECOGNIZED',
                'detail' => 'Persentase kecocokan wajah terlalu rendah: ' . $percent . '%',
            ], 200);
        }

        // 5) ambil data siswa dari DB
        $siswa = Siswa::where('nis', $nis)->first();
        if (!$siswa) {
            return response()->json([
                'ok' => false,
                'message' => 'SISWA_NOT_FOUND',
                'detail' => 'NIS hasil recognize tidak ditemukan di database.',
            ], 200);
        }

        // 6) simpan absensi
        Absensi::create([
            'nis'     => $siswa->nis,
            'nama'    => $siswa->nama,
            'kelas'   => $siswa->kelas,
            'percent' => $percent,
        ]);

        // 7) response sukses
        return response()->json([
            'ok' => true,
            'message' => 'ABSENSI_SAVED',
            'results' => [[
                'id' => $siswa->nis,
                'name' => $siswa->nama,
                'percent' => $percent,
            ]]
        ]);
    }
}
