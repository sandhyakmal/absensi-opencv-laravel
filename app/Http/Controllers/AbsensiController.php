<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\FaceClient;
use App\Models\Siswa;
use App\Models\Absensi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class AbsensiController extends Controller
{
    public function inIndex()
    {
        return view('admin.pages.absensi.indexIn');
    }

    public function recognizeIn(Request $request, FaceClient $faceApi)
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

        // 6) cek apakah sudah absen hari ini
        $today = Carbon::now('Asia/Jakarta')->toDateString();

        $sudahAbsen = Absensi::where('nis', $siswa->nis)
            ->where('type_absensi', 'Masuk')
            ->whereDate('created_at', $today)
            ->exists();

        if ($sudahAbsen) {
            return response()->json([
                'ok' => false,
                'message' => 'ALREADY_ATTENDANCE',
                'detail' => 'Siswa sudah melakukan absensi hari ini.',
            ], 200);
        }

        // 7) simpan absensi
        $type_absensi = 'Masuk';
        Absensi::create([
            'nis'           => $siswa->nis,
            'nama'          => $siswa->nama,
            'kelas'         => $siswa->kelas,
            'percent'       => $percent,
            'type_absensi'  => $type_absensi,
        ]);

        // 8) kirim notifikasi absensi
        if ($siswa->chat_id) {

            $tanggal = Carbon::now('Asia/Jakarta')->translatedFormat('d F Y');
            $jam     = Carbon::now('Asia/Jakarta')->format('H:i');

            $message =
                    "📢 *Notifikasi Absensi Siswa*\n\n" .
                    "```\n" .
                    "Nama           : {$siswa->nama}\n" .
                    "NIS            : {$siswa->nis}\n" .
                    "Kelas          : {$siswa->kelas}\n" .
                    "Tanggal        : {$tanggal}\n" .
                    "Jam            : {$jam} WIB\n" .
                    "Akurasi Wajah  : {$percent}%\n" .
                    "Type Absensi   : {$type_absensi}\n" .
                    "```\n\n" .
                    "✅ Siswa telah melakukan absensi masuk hari ini.\n" .
                    "Terima kasih.";

                try {

                    $response = Http::timeout(5)
                        ->connectTimeout(5)
                        ->post(
                            "https://api.telegram.org/bot" . config('services.telegram.bot_token') . "/sendMessage",
                            [
                                'chat_id' => $siswa->chat_id,
                                'text' => $message,
                                'parse_mode' => 'Markdown'
                            ]
                        );

                    if (!$response->successful()) {
                        \Log::error('Telegram response error: ' . $response->body());
                    }

                } catch (\Exception $e) {
                    \Log::error('Telegram gagal: ' . $e->getMessage());
                }

            }

        // 9) response sukses
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

    public function outIndex()
    {
        return view('admin.pages.absensi.indexOut');
    }

    public function recognizeOut(Request $request, FaceClient $faceApi)
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

        // 6) cek apakah sudah absen hari ini
        $today = Carbon::now('Asia/Jakarta')->toDateString();

        $sudahAbsen = Absensi::where('nis', $siswa->nis)
            ->where('type_absensi', 'Keluar')
            ->whereDate('created_at', $today)
            ->exists();

        if ($sudahAbsen) {
            return response()->json([
                'ok' => false,
                'message' => 'ALREADY_ATTENDANCE',
                'detail' => 'Siswa sudah melakukan absensi masuk hari ini.',
            ], 200);
        }

        // 7) simpan absensi
        $type_absensi = 'Keluar';
        Absensi::create([
            'nis'     => $siswa->nis,
            'nama'    => $siswa->nama,
            'kelas'   => $siswa->kelas,
            'percent' => $percent,
            'type_absensi' => $type_absensi,
        ]);

        // 8) kirim notifikasi absensi
        if ($siswa->chat_id) {

            $tanggal = Carbon::now('Asia/Jakarta')->translatedFormat('d F Y');
            $jam     = Carbon::now('Asia/Jakarta')->format('H:i');

            $message =
                    "📢 *Notifikasi Absensi Siswa*\n\n" .
                    "```\n" .
                    "Nama           : {$siswa->nama}\n" .
                    "NIS            : {$siswa->nis}\n" .
                    "Kelas          : {$siswa->kelas}\n" .
                    "Tanggal        : {$tanggal}\n" .
                    "Jam            : {$jam} WIB\n" .
                    "Akurasi Wajah  : {$percent}%\n" .
                    "Type Absensi   : {$type_absensi}\n" .
                    "```\n\n" .
                    "✅ Siswa telah melakukan absensi keluar hari ini.\n" .
                    "Terima kasih.";

                try {

                    $response = Http::timeout(5)
                        ->connectTimeout(5)
                        ->post(
                            "https://api.telegram.org/bot" . config('services.telegram.bot_token') . "/sendMessage",
                            [
                                'chat_id' => $siswa->chat_id,
                                'text' => $message,
                                'parse_mode' => 'Markdown'
                            ]
                        );

                    if (!$response->successful()) {
                        \Log::error('Telegram response error: ' . $response->body());
                    }

                } catch (\Exception $e) {
                    \Log::error('Telegram gagal: ' . $e->getMessage());
                }

            }

        // 9) response sukses
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
