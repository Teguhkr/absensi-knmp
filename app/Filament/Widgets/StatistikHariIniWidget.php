<?php

namespace App\Filament\Widgets;

use App\Models\Absensi;
use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatistikHariIniWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $today = Carbon::today();
        
        $totalPegawai = User::where('role', 'pegawai')->where('is_active', true)->count();
        $hadir = Absensi::whereDate('tanggal', $today)->where('status', 'hadir')->count();
        $terlambat = Absensi::whereDate('tanggal', $today)->where('status', 'terlambat')->count();
        $izin = Absensi::whereDate('tanggal', $today)->whereIn('status', ['izin', 'sakit', 'dinas'])->count();
        
        $totalHadir = $hadir + $terlambat;
        $alpha = $totalPegawai - ($totalHadir + $izin);
        
        $persentase = $totalPegawai > 0 ? round(($totalHadir / $totalPegawai) * 100) : 0;

        return [
            Stat::make('Total Pegawai', $totalPegawai)
                ->description('Pegawai aktif')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary')
                ->extraAttributes(['class' => 'stat-card-premium stat-total-pegawai']),
                
            Stat::make('Presensi Hari Ini', $totalHadir)
                ->description($hadir . ' Tepat waktu, ' . $terlambat . ' Terlambat')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->extraAttributes(['class' => 'stat-card-premium stat-kehadiran']),
                
            Stat::make('Cuti / Sakit / Penugasan', $izin)
                ->description('Pegawai cuti/sakit/penugasan')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('warning')
                ->extraAttributes(['class' => 'stat-card-premium stat-izin']),
                
            Stat::make('Tingkat Presensi', $persentase . '%')
                ->description($alpha . ' Pegawai Alpha')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($persentase >= 80 ? 'success' : 'danger')
                ->extraAttributes(['class' => 'stat-card-premium stat-tingkat-kehadiran']),
        ];
    }
}
