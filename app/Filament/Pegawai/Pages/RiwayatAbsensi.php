<?php

namespace App\Filament\Pegawai\Pages;

use App\Models\Absensi;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;

class RiwayatAbsensi extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'Riwayat Absensi';
    protected static ?string $title = 'Riwayat Absensi Anda';
    protected static ?int $navigationSort = 2;
    
    protected string $view = 'filament.pegawai.pages.riwayat-absensi';

    public function table(Table $table): Table
    {
        return $table
            ->query(Absensi::query()->where('user_id', Auth::id()))
            ->columns([
                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('l, d M Y')
                    ->sortable(),
                TextColumn::make('jam_masuk')
                    ->label('Jam Masuk')
                    ->time('H:i')
                    ->placeholder('-'),
                TextColumn::make('jam_pulang')
                    ->label('Jam Pulang')
                    ->time('H:i')
                    ->placeholder('-'),
                TextColumn::make('durasi_kerja')
                    ->label('Durasi'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'hadir'     => 'success',
                        'terlambat' => 'warning',
                        'izin'      => 'info',
                        'sakit'     => 'warning',
                        'alpha'     => 'danger',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match($state) {
                        'hadir'     => 'Hadir',
                        'terlambat' => 'Terlambat',
                        'izin'      => 'Izin',
                        'sakit'     => 'Sakit',
                        'alpha'     => 'Alpha',
                        default     => $state,
                    }),
                IconColumn::make('qr_scan_masuk')
                    ->label('Scan Masuk')
                    ->boolean(),
                IconColumn::make('qr_scan_pulang')
                    ->label('Scan Pulang')
                    ->boolean(),
            ])
            ->defaultSort('tanggal', 'desc')
            ->filters([
                Filter::make('tanggal')
                    ->form([
                        DatePicker::make('dari')->label('Dari Tanggal'),
                        DatePicker::make('sampai')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['dari'], fn ($q) => $q->whereDate('tanggal', '>=', $data['dari']))
                            ->when($data['sampai'], fn ($q) => $q->whereDate('tanggal', '<=', $data['sampai']));
                    }),
            ]);
    }
}
