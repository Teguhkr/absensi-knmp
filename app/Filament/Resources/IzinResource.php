<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IzinResource\Pages;
use App\Models\Izin;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class IzinResource extends Resource
{
    protected static ?string $model = Izin::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Cuti & Penugasan';
    protected static ?string $modelLabel = 'Cuti & Penugasan';
    protected static ?string $pluralModelLabel = 'Cuti & Penugasan';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationBadgeColor = 'warning';

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'pending')
            ->orWhere('req_lokasi_status', 'pending')
            ->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Detail Pengajuan')
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->label('Pegawai')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                    Forms\Components\Select::make('jenis')
                        ->label('Jenis')
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
                        ->required(),
                    Forms\Components\DatePicker::make('tanggal_selesai')
                        ->label('Tanggal Selesai')
                        ->required(),
                    Forms\Components\Textarea::make('alasan')
                        ->label('Alasan')
                        ->required()
                        ->columnSpanFull(),
                    Forms\Components\FileUpload::make('lampiran')
                        ->label('Lampiran (Surat/Dokumen)')
                        ->disk('public')
                        ->directory('izin/lampiran')
                        ->acceptedFileTypes(['image/*', 'application/pdf'])
                        ->downloadable()
                        ->openable()
                        ->columnSpanFull(),
                ])->columns(2),

            // ===== LOKASI PENUGASAN =====
            Section::make('📍 Lokasi Penugasan')
                ->description('Koordinat GPS lokasi tempat penugasan berlangsung.')
                ->schema([
                    Forms\Components\TextInput::make('latitude')
                        ->label('Latitude')
                        ->numeric()
                        ->placeholder('-6.200000'),
                    Forms\Components\TextInput::make('longitude')
                        ->label('Longitude')
                        ->numeric()
                        ->placeholder('106.816666'),
                ])
                ->columns(2)
                ->visible(fn ($get) => $get('jenis') === 'dinas')
                ->collapsible(),

            Section::make('Keputusan Admin')
                ->schema([
                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options([
                            'pending'  => 'Menunggu',
                            'approved' => 'Disetujui',
                            'rejected' => 'Ditolak',
                        ])
                        ->required()
                        ->default('pending'),
                    Forms\Components\Textarea::make('catatan_admin')
                        ->label('Catatan Admin')
                        ->columnSpanFull(),
                ])->columns(1),
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
                    ->date('d M Y')
                    ->sortable(),
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
                    ->label('Ubah Lokasi')
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
                    ->placeholder('-'),
                Tables\Columns\IconColumn::make('lampiran')
                    ->label('Bukti')
                    ->icon(fn ($state) => $state ? 'heroicon-o-paper-clip' : 'heroicon-o-minus')
                    ->color(fn ($state) => $state ? 'success' : 'gray')
                    ->url(fn ($state) => $state ? asset('storage/' . $state) : null)
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Diajukan')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak']),
                Tables\Filters\SelectFilter::make('jenis')
                    ->options([
                        'izin'  => 'Cuti',
                        'sakit' => 'Sakit',
                        'dinas' => 'Penugasan',
                    ]),
                Tables\Filters\Filter::make('req_lokasi_pending')
                    ->label('Ada Request Ubah Lokasi')
                    ->query(fn (Builder $query) => $query->where('req_lokasi_status', 'pending')),
            ])
            ->actions([
                // ---- APPROVE PENUGASAN ----
                \Filament\Actions\Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Izin $record) {
                        $record->update([
                            'status'      => 'approved',
                            'approved_by' => auth()->id(),
                        ]);
                        // Buat/update record absensi untuk setiap hari penugasan
                        $start = Carbon::parse($record->tanggal_mulai);
                        $end   = Carbon::parse($record->tanggal_selesai);
                        while ($start->lte($end)) {
                            $existing = \App\Models\Absensi::where('user_id', $record->user_id)
                                ->whereDate('tanggal', $start->toDateString())
                                ->first();
                            if ($existing) {
                                $existing->update([
                                    'status'     => $record->jenis,
                                    'keterangan' => $record->alasan,
                                ]);
                            } else {
                                \App\Models\Absensi::create([
                                    'user_id'    => $record->user_id,
                                    'tanggal'    => $start->toDateString(),
                                    'status'     => $record->jenis,
                                    'keterangan' => $record->alasan,
                                ]);
                            }
                            $start->addDay();
                        }
                        Notification::make()->success()->title('Pengajuan disetujui.')->send();
                    })
                    ->visible(fn (Izin $record) => $record->status === 'pending'),

                // ---- REJECT PENUGASAN ----
                \Filament\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('catatan_admin')
                            ->label('Alasan Penolakan')
                            ->required(),
                    ])
                    ->action(function (Izin $record, array $data) {
                        $record->update([
                            'status'        => 'rejected',
                            'approved_by'   => auth()->id(),
                            'catatan_admin' => $data['catatan_admin'],
                        ]);
                        Notification::make()->danger()->title('Pengajuan ditolak.')->send();
                    })
                    ->visible(fn (Izin $record) => $record->status === 'pending'),

                // ---- APPROVE PERUBAHAN LOKASI ----
                \Filament\Actions\Action::make('approve_lokasi')
                    ->label('✅ Setujui Lokasi Baru')
                    ->icon('heroicon-o-map-pin')
                    ->color('success')
                    ->modalHeading('Setujui Perubahan Lokasi Penugasan')
                    ->form(fn (Izin $record) => [
                        Forms\Components\Placeholder::make('info_lokasi_lama')
                            ->label('📍 Lokasi Lama (Saat Ini)')
                            ->content(fn () => $record->latitude && $record->longitude
                                ? number_format($record->latitude, 6) . ', ' . number_format($record->longitude, 6)
                                : '(Belum diset)'
                            ),
                        Forms\Components\Placeholder::make('info_lokasi_baru')
                            ->label('📍 Lokasi Baru yang Diajukan')
                            ->content(fn () => number_format($record->req_latitude, 6) . ', ' . number_format($record->req_longitude, 6)),
                        Forms\Components\Placeholder::make('info_alasan')
                            ->label('💬 Alasan Perubahan Lokasi')
                            ->content(fn () => $record->req_lokasi_alasan ?? '-'),
                        Forms\Components\Textarea::make('req_lokasi_catatan')
                            ->label('Catatan Persetujuan (opsional)')
                            ->placeholder('Catatan untuk pegawai...'),
                    ])
                    ->action(function (Izin $record, array $data) {
                        $record->update([
                            'latitude'           => $record->req_latitude,
                            'longitude'          => $record->req_longitude,
                            'req_lokasi_status'  => 'approved',
                            'req_lokasi_catatan' => $data['req_lokasi_catatan'] ?? null,
                        ]);
                        Notification::make()->success()->title('Perubahan lokasi disetujui. Koordinat penugasan telah diperbarui.')->send();
                    })
                    ->visible(fn (Izin $record) => $record->req_lokasi_status === 'pending' && $record->jenis === 'dinas'),

                // ---- REJECT PERUBAHAN LOKASI ----
                \Filament\Actions\Action::make('reject_lokasi')
                    ->label('❌ Tolak Lokasi Baru')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->modalHeading('Tolak Perubahan Lokasi Penugasan')
                    ->form(fn (Izin $record) => [
                        Forms\Components\Placeholder::make('info_lokasi_baru')
                            ->label('📍 Lokasi Baru yang Diajukan')
                            ->content(fn () => number_format($record->req_latitude, 6) . ', ' . number_format($record->req_longitude, 6)),
                        Forms\Components\Placeholder::make('info_alasan')
                            ->label('💬 Alasan dari Pegawai')
                            ->content(fn () => $record->req_lokasi_alasan ?? '-'),
                        Forms\Components\Textarea::make('req_lokasi_catatan')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->placeholder('Jelaskan alasan penolakan...'),
                    ])
                    ->action(function (Izin $record, array $data) {
                        $record->update([
                            'req_lokasi_status'  => 'rejected',
                            'req_lokasi_catatan' => $data['req_lokasi_catatan'],
                        ]);
                        Notification::make()->warning()->title('Perubahan lokasi ditolak.')->send();
                    })
                    ->visible(fn (Izin $record) => $record->req_lokasi_status === 'pending' && $record->jenis === 'dinas'),

                \Filament\Actions\ViewAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListIzin::route('/'),
            'create' => Pages\CreateIzin::route('/create'),
            'view'   => Pages\ViewIzin::route('/{record}'),
            'edit'   => Pages\EditIzin::route('/{record}/edit'),
        ];
    }
}
