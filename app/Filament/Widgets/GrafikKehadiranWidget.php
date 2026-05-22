<?php

namespace App\Filament\Widgets;

use App\Models\Absensi;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Widgets\ChartWidget;

class GrafikKehadiranWidget extends ChartWidget
{
    protected ?string $heading = 'Grafik Kehadiran (7 Hari Terakhir)';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $startDate = Carbon::now()->subDays(6)->startOfDay();
        $endDate = Carbon::now()->endOfDay();
        
        $period = CarbonPeriod::create($startDate, '1 day', $endDate);
        
        $labels = [];
        $hadirData = [];
        $terlambatData = [];
        $izinData = [];

        foreach ($period as $date) {
            $dateString = $date->toDateString();
            $labels[] = $date->format('d M');
            
            $hadirData[] = Absensi::whereDate('tanggal', $dateString)->where('status', 'hadir')->count();
            $terlambatData[] = Absensi::whereDate('tanggal', $dateString)->where('status', 'terlambat')->count();
            $izinData[] = Absensi::whereDate('tanggal', $dateString)->whereIn('status', ['izin', 'sakit'])->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Hadir',
                    'data' => $hadirData,
                    'backgroundColor' => '#10b981', // green
                    'borderColor' => '#10b981',
                ],
                [
                    'label' => 'Terlambat',
                    'data' => $terlambatData,
                    'backgroundColor' => '#f59e0b', // amber
                    'borderColor' => '#f59e0b',
                ],
                [
                    'label' => 'Izin / Sakit',
                    'data' => $izinData,
                    'backgroundColor' => '#3b82f6', // blue
                    'borderColor' => '#3b82f6',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
