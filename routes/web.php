<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReportController;
use App\Http\Middleware\AuthWithMessage;
use App\Http\Controllers\AbsensiController;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware(AuthWithMessage::class)->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('data-siswa')->name('siswa.')->group(function () {
        Route::get('/', [SiswaController::class, 'index'])->name('index');
        Route::get('/create', [SiswaController::class, 'create'])->name('create');
        Route::post('/', [SiswaController::class, 'store'])->name('store');
        Route::get('/{siswa}/edit', [SiswaController::class, 'edit'])->name('edit');
        Route::put('/{siswa}', [SiswaController::class, 'update'])->name('update');
        Route::delete('/{siswa}', [SiswaController::class, 'destroy'])->name('destroy');
    });

     Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/siswa', [ReportController::class, 'siswa'])->name('siswa');
            Route::get('/siswa/export', [ReportController::class, 'siswaExport'])->name('siswa.export');
            Route::get('/absensi', [ReportController::class, 'absensi'])->name('absensi');
            Route::get('/absensi/export', [ReportController::class, 'absensiExport'])->name('absensi.export');
    });

    Route::prefix('absensi')->name('absensi.')->group(function () {
        Route::get('/', [AbsensiController::class, 'index'])->name('index');
        Route::post('/recognize', [AbsensiController::class, 'recognize'])->name('recognize');
    });

});
