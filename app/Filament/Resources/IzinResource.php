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
use Illuminate\Database\Eloquent\Builder;

class IzinResource extends Resource
{
    protected static ?string $model = Izin::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Izin & Sakit';
    protected static ?string $modelLabel = 'Pengajuan Izin';
    protected static ?string $pluralModelLabel = 'Izin & Sakit';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationBadgeColor = 'warning';

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'pending')->count();
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
                            'izin' => 'Izin',
                            'sakit' => 'Sakit',
                            'dinas' => 'Izin Dinas',
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
                        'info' => 'izin',
                        'warning' => 'sakit',
                        'success' => 'dinas',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'izin' => 'Izin',
                        'sakit' => 'Sakit',
                        'dinas' => 'Izin Dinas',
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
                        'izin' => 'Izin',
                        'sakit' => 'Sakit',
                        'dinas' => 'Izin Dinas',
                    ]),
            ])
            ->actions([
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
                        // Buat/update record absensi untuk setiap hari izin
                        $start = Carbon::parse($record->tanggal_mulai);
                        $end   = Carbon::parse($record->tanggal_selesai);
                        while ($start->lte($end)) {
                            $existing = \App\Models\Absensi::where('user_id', $record->user_id)
                                ->whereDate('tanggal', $start->toDateString())
                                ->first();
                            if ($existing) {
                                $existing->update([
                                    'status'    => $record->jenis,
                                    'keterangan'=> $record->alasan,
                                ]);
                            } else {
                                \App\Models\Absensi::create([
                                    'user_id'   => $record->user_id,
                                    'tanggal'   => $start->toDateString(),
                                    'status'    => $record->jenis,
                                    'keterangan'=> $record->alasan,
                                ]);
                            }
                            $start->addDay();
                        }
                    })
                    ->visible(fn (Izin $record) => $record->status === 'pending'),

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
                    })
                    ->visible(fn (Izin $record) => $record->status === 'pending'),

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
