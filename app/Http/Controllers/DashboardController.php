<?php

namespace App\Http\Controllers;
use App\Models\Siswa;
use App\Models\Absensi;

// use Illuminate\Http\Request;

class DashboardController extends Controller
{
     public function index()
    {
        $totalSiswa = Siswa::count();
        $totalAbsensiMasuk = Absensi::whereDate('created_at', now())
                        ->where('type_absensi', 'Masuk')
                        ->count();
        $totalAbsensiKeluar = Absensi::whereDate('created_at', now())
                        ->where('type_absensi', 'Masuk')
                        ->count();

        return view('admin.dashboard', compact('totalSiswa', 'totalAbsensiMasuk', 'totalAbsensiKeluar'));
    }
}
