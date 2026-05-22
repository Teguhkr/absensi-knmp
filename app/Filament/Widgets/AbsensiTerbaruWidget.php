<?php

namespace App\Filament\Widgets;

use App\Models\Absensi;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class AbsensiTerbaruWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Absensi Terbaru Hari Ini';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Absensi::query()
                    ->whereDate('tanggal', Carbon::today())
                    ->latest('updated_at')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama Pegawai'),
                Tables\Columns\TextColumn::make('jam_masuk')
                    ->label('Jam Masuk')
                    ->time('H:i')
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('jam_pulang')
                    ->label('Jam Pulang')
                    ->time('H:i')
                    ->placeholder('-'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'hadir',
                        'warning' => 'terlambat',
                        'info'    => 'izin',
                        'warning' => 'sakit',
                        'danger'  => 'alpha',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'hadir'     => 'Hadir',
                        'terlambat' => 'Terlambat',
                        'izin'      => 'Izin',
                        'sakit'     => 'Sakit',
                        'alpha'     => 'Alpha',
                        default     => $state,
                    }),
            ])
            ->paginated(false);
    }
}
