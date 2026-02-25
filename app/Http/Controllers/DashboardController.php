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
        $totalAbsensi = Absensi::whereDate('created_at', now())->count();

        return view('admin.dashboard', compact('totalSiswa', 'totalAbsensi'));
    }
}
