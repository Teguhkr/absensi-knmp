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
    public const PEJABAT_PEMBUAT_KOMITMEN = [
        'Andi Cahyono, S.T' => 'Andi Cahyono, S.T',
        'Agus Lubis Fitriansyah, S.T., M.T.' => 'Agus Lubis Fitriansyah, S.T., M.T.',
        'Fauzan Idris Maspeke, S.T, M.Si' => 'Fauzan Idris Maspeke, S.T, M.Si',
        'R. Tono Amboro, S.St.Pi., M.E.S.M.' => 'R. Tono Amboro, S.St.Pi., M.E.S.M.',
        'Dicky Rachmanzah, S.E.' => 'Dicky Rachmanzah, S.E.',
        'Bibin Wibisono, S.T.' => 'Bibin Wibisono, S.T.',
        'Didik Sukoco S.E.' => 'Didik Sukoco S.E.',
        'Hendra Pramono, S.St. Pi.' => 'Hendra Pramono, S.St. Pi.',
        'Achmad Fauzie S.Pi, M.Si' => 'Achmad Fauzie S.Pi, M.Si',
        'Arif Jaelani Al Mutaqin, S.E' => 'Arif Jaelani Al Mutaqin, S.E',
        'Umar Soleh, S.Pi., M.Si.' => 'Umar Soleh, S.Pi., M.Si.',
        'Yanwar Amri Yasman, S.St.Pi., M.Si.' => 'Yanwar Amri Yasman, S.St.Pi., M.Si.',
    ];

    public static function getPejabatPembuatKomitmenList(): array
    {
        return self::PEJABAT_PEMBUAT_KOMITMEN;
    }

    public function download(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $request->validate([
            'month' => 'required|string|size:2',
            'year'  => 'required|string|size:4',
            'ppk'   => 'nullable|string',
        ]);

        $month = $request->query('month');
        $year  = $request->query('year');
        $ppk   = $request->query('ppk', 'Fauzan Idris Maspeke, S.T, M.Si');
        if (empty($ppk)) {
            $ppk = 'Fauzan Idris Maspeke, S.T, M.Si';
        }

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
            'ppk'           => $ppk,
        ]);

        $pdf->setPaper('a4', 'portrait');

        $fileName = 'Riwayat_Presensi_' . str_replace(' ', '_', $user->name) . "_{$monthName}_{$year}.pdf";

        return $pdf->download($fileName);
    }
}
