<?php

namespace App\Filament\Widgets;

use App\Models\Absensi;
use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class RasioKehadiranHariIniWidget extends ChartWidget
{
    protected ?string $heading = 'Rasio Kehadiran Hari Ini';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $today = Carbon::today();
        
        $totalPegawai = User::where('role', 'pegawai')->where('is_active', true)->count();
        $hadir = Absensi::whereDate('tanggal', $today)->where('status', 'hadir')->count();
        $terlambat = Absensi::whereDate('tanggal', $today)->where('status', 'terlambat')->count();
        $izin = Absensi::whereDate('tanggal', $today)->whereIn('status', ['izin', 'sakit', 'dinas'])->count();
        
        $totalHadir = $hadir + $terlambat;
        $alpha = max(0, $totalPegawai - ($totalHadir + $izin));

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Pegawai',
                    'data' => [$hadir, $terlambat, $izin, $alpha],
                    'backgroundColor' => [
                        '#10b981', // emerald green (Hadir)
                        '#f59e0b', // amber (Terlambat)
                        '#0ea5e9', // sky blue (Izin/Sakit/Dinas)
                        '#f43f5e', // rose red (Alpha/Belum Absen)
                    ],
                    'borderColor' => [
                        '#10b981',
                        '#f59e0b',
                        '#0ea5e9',
                        '#f43f5e',
                    ],
                ],
            ],
            'labels' => [
                'Hadir (' . $hadir . ')',
                'Terlambat (' . $terlambat . ')',
                'Izin / Sakit / Dinas (' . $izin . ')',
                'Alpha / Belum Absen (' . $alpha . ')',
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
