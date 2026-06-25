<?php

namespace App\Filament\Resources\LaporanHarianResource\Pages;

use App\Filament\Resources\LaporanHarianResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms;
use App\Models\LaporanHarian;

class ListLaporanHarians extends ListRecords
{
    protected static string $resource = LaporanHarianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('cetak_bulanan')
                ->label('Cetak PDF Bulanan')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->form([
                    Forms\Components\Select::make('user_id')
                        ->label('Pegawai')
                        ->options(fn () => \App\Models\User::where('role', 'pegawai')->orderBy('name')->pluck('name', 'id')->toArray())
                        ->searchable()
                        ->required(),
                    Forms\Components\Select::make('bulan')
                        ->label('Bulan')
                        ->options([
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
                        ])
                        ->default(now()->format('m'))
                        ->required(),
                    Forms\Components\Select::make('tahun')
                        ->label('Tahun')
                        ->options(function () {
                            $currentYear = now()->year;
                            $years = range($currentYear - 5, $currentYear + 2);
                            return array_combine($years, $years);
                        })
                        ->default(now()->year)
                        ->required(),
                ])
                ->action(function (array $data) {
                    $userId = $data['user_id'];
                    $bulan = $data['bulan'];
                    $tahun = $data['tahun'];

                    $exists = LaporanHarian::where('user_id', $userId)
                        ->whereMonth('tanggal', $bulan)
                        ->whereYear('tanggal', $tahun)
                        ->exists();

                    if (!$exists) {
                        \Filament\Notifications\Notification::make()
                            ->danger()
                            ->title('Laporan Tidak Ditemukan')
                            ->body('Tidak ada data laporan harian untuk pegawai, bulan, dan tahun tersebut.')
                            ->send();

                        return;
                    }

                    return redirect()->route('laporan-harian.pdf', [
                        'user_id' => $userId,
                        'month' => $bulan,
                        'year' => $tahun,
                    ]);
                }),
            Actions\CreateAction::make(),
        ];
    }
}
