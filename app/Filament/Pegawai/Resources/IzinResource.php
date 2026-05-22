<?php

namespace App\Filament\Pegawai\Resources;

use App\Filament\Pegawai\Resources\IzinResource\Pages;
use App\Models\Izin;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class IzinResource extends Resource
{
    protected static ?string $model = Izin::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-plus';
    protected static ?string $navigationLabel = 'Pengajuan Izin';
    protected static ?string $modelLabel = 'Izin';
    protected static ?string $pluralModelLabel = 'Pengajuan Izin';
    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Form Pengajuan Izin / Sakit')
                    ->schema([
                        Forms\Components\Hidden::make('user_id')
                            ->default(fn () => Auth::id()),
                        Forms\Components\Select::make('jenis')
                            ->label('Jenis Pengajuan')
                            ->options([
                                'izin' => 'Izin',
                                'sakit' => 'Sakit',
                            ])
                            ->required(),
                        Forms\Components\DatePicker::make('tanggal_mulai')
                            ->label('Tanggal Mulai')
                            ->required()
                            ->default(now()),
                        Forms\Components\DatePicker::make('tanggal_selesai')
                            ->label('Tanggal Selesai')
                            ->required()
                            ->default(now()),
                        Forms\Components\Textarea::make('alasan')
                            ->label('Alasan / Keterangan')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('lampiran')
                            ->label('Lampiran (Surat Dokter/Bukti)')
                            ->disk('public')
                            ->directory('izin/lampiran')
                            ->acceptedFileTypes(['image/*', 'application/pdf'])
                            ->downloadable()
                            ->openable()
                            ->columnSpanFull(),
                        Forms\Components\Hidden::make('status')
                            ->default('pending'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\BadgeColumn::make('jenis')
                    ->label('Jenis')
                    ->colors(['info' => 'izin', 'warning' => 'sakit'])
                    ->formatStateUsing(fn ($state) => ucfirst($state)),
                Tables\Columns\TextColumn::make('tanggal_mulai')
                    ->label('Mulai')
                    ->date('d M Y'),
                Tables\Columns\TextColumn::make('tanggal_selesai')
                    ->label('Selesai')
                    ->date('d M Y'),
                Tables\Columns\TextColumn::make('jumlah_hari')
                    ->label('Hari')
                    ->suffix(' hari')
                    ->getStateUsing(fn ($record) => $record->tanggal_mulai->diffInDays($record->tanggal_selesai) + 1),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger'  => 'rejected',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'pending'  => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        default    => $state,
                    }),
                Tables\Columns\IconColumn::make('lampiran')
                    ->label('Bukti')
                    ->icon(fn ($state) => $state ? 'heroicon-o-paper-clip' : 'heroicon-o-minus')
                    ->color(fn ($state) => $state ? 'success' : 'gray')
                    ->url(fn ($state) => $state ? asset('storage/' . $state) : null)
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('catatan_admin')
                    ->label('Catatan Admin')
                    ->limit(30),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                \Filament\Actions\ViewAction::make()
                    ->slideOver()
                    ->modalWidth('2xl'),
                \Filament\Actions\DeleteAction::make()
                    ->visible(fn (Izin $record) => $record->status === 'pending')
                    ->label('Batal'),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', Auth::id());
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListIzins::route('/'),
            'create' => Pages\CreateIzin::route('/create'),
        ];
    }
}
