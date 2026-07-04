<?php

use App\Http\Controllers\LaporanHarianPdfController;
use App\Http\Controllers\QrAbsensiController;
use App\Http\Controllers\RiwayatAbsensiPdfController;
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

// Route untuk cetak PDF Riwayat Presensi
Route::get('/riwayat-absensi/pdf', [RiwayatAbsensiPdfController::class, 'download'])
    ->name('riwayat-absensi.pdf')
    ->middleware(['auth']);

use App\Http\Controllers\PushSubscriptionController;

// Route untuk push notifications subskripsi browser
Route::middleware(['auth'])->group(function () {
    Route::post('/push-subscriptions', [PushSubscriptionController::class, 'store'])->name('push-subscriptions.store');
    Route::post('/push-subscriptions/delete', [PushSubscriptionController::class, 'destroy'])->name('push-subscriptions.destroy');
});


// Route bawaan Breeze kita hilangkan karena tidak digunakan (Autentikasi di-handle oleh Filament)
