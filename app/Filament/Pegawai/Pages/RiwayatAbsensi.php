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
use Filament\Actions\Action;
use Filament\Forms\Components\Select;

class RiwayatAbsensi extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'Riwayat Presensi';
    protected static ?string $title = 'Riwayat Presensi Anda';
    protected static ?int $navigationSort = 2;
    
    protected string $view = 'filament.pegawai.pages.riwayat-absensi';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('cetak_pdf')
                ->label('Cetak PDF')
                ->color('success')
                ->icon('heroicon-o-document-arrow-down')
                ->form([
                    Select::make('month')
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
                    Select::make('year')
                        ->label('Tahun')
                        ->options(
                            collect(range(now()->subYears(5)->year, now()->year))
                                ->mapWithKeys(fn ($y) => [$y => $y])
                                ->toArray()
                        )
                        ->default(now()->year)
                        ->required(),
                ])
                ->action(function (array $data) {
                    return redirect()->route('riwayat-absensi.pdf', [
                        'month' => $data['month'],
                        'year'  => $data['year'],
                    ]);
                }),
        ];
    }

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
                        'dinas'     => 'success',
                        'alpha'     => 'danger',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match($state) {
                        'hadir'     => 'Hadir',
                        'terlambat' => 'Terlambat',
                        'izin'      => 'Cuti',
                        'sakit'     => 'Sakit',
                        'dinas'     => 'Penugasan',
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

