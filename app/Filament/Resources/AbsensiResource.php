<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AbsensiResource\Pages;
use App\Models\Absensi;
use App\Models\User;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class AbsensiResource extends Resource
{
    protected static ?string $model = Absensi::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Data Absensi';
    protected static ?string $modelLabel = 'Absensi';
    protected static ?string $pluralModelLabel = 'Data Absensi';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Data Absensi')
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->label('Pegawai')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                    Forms\Components\DatePicker::make('tanggal')
                        ->label('Tanggal')
                        ->required()
                        ->default(now()),
                    Forms\Components\TimePicker::make('jam_masuk')
                        ->label('Jam Masuk')
                        ->seconds(false),
                    Forms\Components\TimePicker::make('jam_pulang')
                        ->label('Jam Pulang')
                        ->seconds(false),
                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options([
                            'hadir'     => 'Hadir',
                            'terlambat' => 'Terlambat',
                            'izin'      => 'Izin',
                            'sakit'     => 'Sakit',
                            'alpha'     => 'Alpha',
                        ])
                        ->required()
                        ->default('hadir'),
                    Forms\Components\Textarea::make('keterangan')
                        ->label('Keterangan')
                        ->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama Pegawai')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.departemen')
                    ->label('Departemen')
                    ->searchable()
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('jam_masuk')
                    ->label('Jam Masuk')
                    ->time('H:i'),
                Tables\Columns\TextColumn::make('jam_pulang')
                    ->label('Jam Pulang')
                    ->time('H:i'),
                Tables\Columns\TextColumn::make('status')
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
                Tables\Columns\IconColumn::make('qr_scan_masuk')
                    ->label('QR Masuk')
                    ->boolean(),
                Tables\Columns\IconColumn::make('qr_scan_pulang')
                    ->label('QR Pulang')
                    ->boolean(),
            ])
            ->defaultSort('tanggal', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'hadir'     => 'Hadir',
                        'terlambat' => 'Terlambat',
                        'izin'      => 'Izin',
                        'sakit'     => 'Sakit',
                        'alpha'     => 'Alpha',
                    ]),
                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'name')
                    ->label('Pegawai')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('tanggal')
                    ->form([
                        Forms\Components\DatePicker::make('dari')->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('sampai')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['dari'], fn ($q) => $q->whereDate('tanggal', '>=', $data['dari']))
                            ->when($data['sampai'], fn ($q) => $q->whereDate('tanggal', '<=', $data['sampai']));
                    }),
                Tables\Filters\Filter::make('hari_ini')
                    ->label('Hari Ini')
                    ->query(fn (Builder $query) => $query->whereDate('tanggal', Carbon::today()))
                    ->toggle(),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAbsensi::route('/'),
            'create' => Pages\CreateAbsensi::route('/create'),
            'edit'   => Pages\EditAbsensi::route('/{record}/edit'),
        ];
    }
}
