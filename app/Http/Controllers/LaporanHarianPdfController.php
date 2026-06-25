<?php

namespace App\Http\Controllers;

use App\Models\LaporanHarian;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanHarianPdfController extends Controller
{
    public function download(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'month' => 'required|string|size:2',
            'year' => 'required|string|size:4',
        ]);

        $userId = $request->query('user_id');
        $month = $request->query('month');
        $year = $request->query('year');

        // Proteksi Keamanan: Pegawai biasa hanya boleh mendownload laporannya sendiri
        if (Auth::user()->role !== 'admin' && Auth::id() != $userId) {
            abort(403, 'Akses ditolak. Anda hanya dapat mengunduh laporan harian Anda sendiri.');
        }

        $user = User::findOrFail($userId);

        $reports = LaporanHarian::where('user_id', $userId)
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->orderBy('tanggal', 'asc')
            ->get();

        if ($reports->isEmpty()) {
            return back()->with('error', 'Tidak ada data laporan harian untuk bulan dan tahun tersebut.');
        }

        // Daftar nama bulan Bahasa Indonesia untuk nama file dan judul
        $months = [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember',
        ];
        $monthName = $months[$month] ?? $month;

        $pdf = Pdf::loadView('pdf.laporan-harian', [
            'user' => $user,
            'reports' => $reports,
            'monthName' => $monthName,
            'month' => $month,
            'year' => $year,
        ]);

        // Atur ukuran kertas ke A4 Portrait
        $pdf->setPaper('a4', 'portrait');

        $fileName = "Laporan_Harian_{$user->name}_{$monthName}_{$year}.pdf";
        // Ganti spasi dengan underscore agar nama file bersih
        $fileName = str_replace(' ', '_', $fileName);

        return $pdf->download($fileName);
    }
}
