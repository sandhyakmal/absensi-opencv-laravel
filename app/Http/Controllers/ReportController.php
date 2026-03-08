<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\SiswaExport;
use App\Exports\AbsensiExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function siswa()
    {
        $months = [
            1  => 'Januari',
            2  => 'Februari',
            3  => 'Maret',
            4  => 'April',
            5  => 'Mei',
            6  => 'Juni',
            7  => 'Juli',
            8  => 'Agustus',
            9  => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
        return view('admin.pages.reports.siswa', compact('months'));
    }

    public function siswaExport(Request $request)
    {

        $validated = $request->validate([
            'kelas' => ['required', 'string'],
        ]);

        $rows = DB::table('siswas')
            ->where('kelas', '=', $validated['kelas'])
            ->selectRaw("
                nis as nis,
                nama as nama,
                kelas as kelas,
                no_hp as no_hp,
                alamat as alamat,
                nama_ortu as nama_ortu,
                no_hp_ortu as no_hp_ortu
            ")
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($r) => [
                'nis'          => $r->nis,
                'nama'          => $r->nama,
                'kelas'         => $r->kelas,
                'no_hp'         => $r->no_hp,
                'alamat'        => $r->alamat,
                'nama_ortu'     => $r->nama_ortu,
                'no_hp_ortu'    => $r->no_hp_ortu,
            ]);

        $filename = "laporan_siswa.xlsx";
        $tanggal = now()->format('d-m-Y');
        $kelas = $validated['kelas'];


        return Excel::download(new SiswaExport($rows, $kelas), $filename);

    }

    public function absensi()
    {
        $months = [
            1  => 'Januari',
            2  => 'Februari',
            3  => 'Maret',
            4  => 'April',
            5  => 'Mei',
            6  => 'Juni',
            7  => 'Juli',
            8  => 'Agustus',
            9  => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
        return view('admin.pages.reports.absensi', compact('months'));
    }

    public function absensiExport(Request $request)
    {

        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
        ]);

        $rows = DB::table('absensi')
            ->whereDate('created_at', $validated['tanggal'])
            ->selectRaw("
                nis as nis,
                nama as nama,
                kelas as kelas,
                created_at as tanggal_absen,
                type_absensi as type_absensi
            ")
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($r) => [
                'nis'           => $r->nis,
                'nama'          => $r->nama,
                'kelas'         => $r->kelas,
                'tanggal_absen' => $r->tanggal_absen,
                'type_absensi'  => $r->type_absensi,
            ]);

        $filename = "laporan_absensi.xlsx";
        $tanggal = $validated['tanggal'];

        return Excel::download(new AbsensiExport($rows, $tanggal), $filename);

    }
}
