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
    protected static ?string $navigationLabel = 'Pengajuan Cuti & Penugasan';
    protected static ?string $modelLabel = 'Cuti & Penugasan';
    protected static ?string $pluralModelLabel = 'Pengajuan Cuti & Penugasan';
    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Form Pengajuan Cuti / Penugasan / Sakit')
                    ->schema([
                        Forms\Components\Hidden::make('user_id')
                            ->default(fn () => Auth::id()),
                        Forms\Components\Select::make('jenis')
                            ->label('Jenis Pengajuan')
                            ->options([
                                'izin'  => 'Cuti',
                                'sakit' => 'Sakit',
                                'dinas' => 'Penugasan',
                            ])
                            ->live()
                            ->required(),
                        Forms\Components\TextInput::make('nomor_spt')
                            ->label('Nomor SPT')
                            ->placeholder('Masukkan nomor SPT...')
                            ->required(fn ($get) => $get('jenis') === 'dinas')
                            ->visible(fn ($get) => $get('jenis') === 'dinas')
                            ->columnSpanFull(),
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
                            ->label('Lampiran (Surat/Bukti)')
                            ->disk('public')
                            ->directory('izin/lampiran')
                            ->acceptedFileTypes(['image/*', 'application/pdf'])
                            ->downloadable()
                            ->openable()
                            ->columnSpanFull(),
                        Forms\Components\Hidden::make('status')
                            ->default('pending'),
                    ])->columns(2),

                // ===== SECTION LOKASI PENUGASAN =====
                Section::make('📍 Lokasi Penugasan')
                    ->description('Masukkan koordinat GPS lokasi tempat penugasan berlangsung. Presensi selama penugasan akan divalidasi di lokasi ini.')
                    ->schema([
                        Forms\Components\Placeholder::make('info_lokasi')
                            ->label('')
                            ->content('Anda dapat mendapatkan koordinat dari Google Maps (klik kanan pada lokasi → "Koordinat ini"). Format: -6.123456, 106.123456')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('latitude')
                            ->label('Latitude (Lintang)')
                            ->placeholder('Contoh: -6.200000')
                            ->numeric()
                            ->required(fn ($get) => $get('jenis') === 'dinas')
                            ->rules(['nullable', 'numeric', 'between:-90,90']),
                        Forms\Components\TextInput::make('longitude')
                            ->label('Longitude (Bujur)')
                            ->placeholder('Contoh: 106.816666')
                            ->numeric()
                            ->required(fn ($get) => $get('jenis') === 'dinas')
                            ->rules(['nullable', 'numeric', 'between:-180,180']),
                    ])
                    ->columns(2)
                    ->visible(fn ($get) => $get('jenis') === 'dinas')
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\BadgeColumn::make('jenis')
                    ->label('Jenis')
                    ->colors([
                        'info'    => 'izin',
                        'warning' => 'sakit',
                        'success' => 'dinas',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'izin'  => 'Cuti',
                        'sakit' => 'Sakit',
                        'dinas' => 'Penugasan',
                        default => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('nomor_spt')
                    ->label('Nomor SPT')
                    ->placeholder('-')
                    ->searchable(),
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
                Tables\Columns\BadgeColumn::make('req_lokasi_status')
                    ->label('Perubahan Lokasi')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger'  => 'rejected',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'pending'  => '⏳ Menunggu',
                        'approved' => '✅ Disetujui',
                        'rejected' => '❌ Ditolak',
                        default    => '-',
                    })
                    ->placeholder('-')
                    ->visible(fn ($livewire) => true),
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
