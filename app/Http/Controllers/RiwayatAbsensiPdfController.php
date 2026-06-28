<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\User;
use App\Models\PengaturanSistem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class RiwayatAbsensiPdfController extends Controller
{
    public function download(Request $request)
    {
        $request->validate([
            'month' => 'required|string|size:2',
            'year'  => 'required|string|size:4',
        ]);

        $month = $request->query('month');
        $year  = $request->query('year');

        // Pegawai hanya bisa cetak milik sendiri
        $userId = Auth::id();
        $user   = User::findOrFail($userId);

        $absensiList = Absensi::where('user_id', $userId)
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->orderBy('tanggal', 'asc')
            ->get();

        $months = [
            '01' => 'Januari',  '02' => 'Februari', '03' => 'Maret',
            '04' => 'April',    '05' => 'Mei',       '06' => 'Juni',
            '07' => 'Juli',     '08' => 'Agustus',   '09' => 'September',
            '10' => 'Oktober',  '11' => 'November',  '12' => 'Desember',
        ];
        $monthName = $months[$month] ?? $month;

        // Buat index by tanggal untuk lookup cepat
        $absensiByDate = $absensiList->keyBy(fn ($a) => Carbon::parse($a->tanggal)->format('Y-m-d'));

        // Buat daftar semua hari kerja dalam bulan tersebut (Senin – Jumat)
        $startDate    = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate      = (clone $startDate)->endOfMonth();

        // Hitung rekapitulasi
        $rekap = [
            'hadir'     => 0,
            'terlambat' => 0,
            'izin'      => 0,
            'sakit'     => 0,
            'dinas'     => 0,
            'alpha'     => 0,
        ];
        foreach ($absensiList as $a) {
            if (isset($rekap[$a->status])) {
                $rekap[$a->status]++;
            }
        }

        $instansi = PengaturanSistem::get('nama_instansi', 'KNMP');

        $pdf = Pdf::loadView('pdf.riwayat-presensi', [
            'user'          => $user,
            'absensiList'   => $absensiList,
            'absensiByDate' => $absensiByDate,
            'monthName'     => $monthName,
            'month'         => $month,
            'year'          => $year,
            'startDate'     => $startDate,
            'endDate'       => $endDate,
            'rekap'         => $rekap,
            'instansi'      => $instansi,
        ]);

        $pdf->setPaper('a4', 'portrait');

        $fileName = 'Riwayat_Presensi_' . str_replace(' ', '_', $user->name) . "_{$monthName}_{$year}.pdf";

        return $pdf->download($fileName);
    }
}
