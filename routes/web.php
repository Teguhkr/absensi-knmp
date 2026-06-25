<?php

use App\Http\Controllers\LaporanHarianPdfController;
use App\Http\Controllers\QrAbsensiController;
use Illuminate\Support\Facades\Route;

// Portal Utama
Route::get('/', function () {
    return view('welcome');
});

// Endpoint untuk QR Code Scanner
Route::get('/absensi/scan/{token}', [QrAbsensiController::class, 'showScanForm'])->name('absensi.scan');
Route::post('/absensi/scan/{token}', [QrAbsensiController::class, 'processScan'])->name('absensi.scan.process');

// Route untuk cetak PDF Laporan Harian
Route::get('/laporan-harian/pdf', [LaporanHarianPdfController::class, 'download'])
    ->name('laporan-harian.pdf')
    ->middleware(['auth']);

// Route bawaan Breeze kita hilangkan karena tidak digunakan (Autentikasi di-handle oleh Filament)
