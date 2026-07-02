<?php

namespace App\Filament\Pegawai\Resources;

use App\Filament\Pegawai\Resources\LaporanHarianResource\Pages;
use App\Models\LaporanHarian;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Group;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class LaporanHarianResource extends Resource
{
    protected static ?string $model = LaporanHarian::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Laporan Harian';
    protected static ?string $modelLabel = 'Laporan Harian';
    protected static ?string $pluralModelLabel = 'Laporan Harian';
    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Group::make([
                    Section::make('Informasi Laporan')
                        ->schema([
                            Forms\Components\Hidden::make('user_id')
                                ->default(fn () => Auth::id()),
                            Forms\Components\DatePicker::make('tanggal')
                                ->label('Tanggal Laporan')
                                ->required()
                                ->minDate(fn ($record) => $record ? null : now()->startOfMonth())
                                ->maxDate(fn ($record) => $record ? null : now())
                                ->rules(function ($record) {
                                    $userId = Auth::id();
                                    $rule = \Illuminate\Validation\Rule::unique('laporan_harians', 'tanggal')
                                        ->where('user_id', $userId);
                                    if ($record) {
                                        $rule->ignore($record->id);
                                    }
                                    return [$rule];
                                })
                                ->validationMessages([
                                    'unique' => 'Laporan untuk tanggal ini sudah ada. Silakan edit laporan yang sudah ada.',
                                ])
                                ->default(now()),
                        ])->columns(1),

                    Section::make('Lokasi KNMP')
                        ->description('Peristiwa di lokasi KNMP (Kosongkan jika bekerja dari kantor)')
                        ->schema([
                            Forms\Components\Repeater::make('lokasi_knmp')
                                ->label('Peristiwa Lokasi')
                                ->schema([
                                    Forms\Components\TextInput::make('peristiwa')
                                        ->label('Peristiwa')
                                        ->required(),
                                    Forms\Components\Select::make('status')
                                        ->label('Status')
                                        ->options([
                                            'Clear' => 'Clear',
                                            'Hold' => 'Hold',
                                            'On-Progress' => 'On-Progress',
                                        ])
                                        ->required(),
                                    Forms\Components\TextInput::make('keterangan')
                                        ->label('Keterangan')
                                        ->required(),
                                    Forms\Components\TextInput::make('tindak_lanjut')
                                        ->label('Tindak Lanjut')
                                        ->required(),
                                ])
                                ->columnSpanFull()
                                ->createItemButtonLabel('Tambah Peristiwa Lokasi'),
                        ]),
                ])->columnSpan(1),

                Group::make([
                    Section::make('Operasional')
                        ->description('Kegiatan operasional harian yang dilaksanakan (Akan tercetak di PDF, dapat ditambahkan dinamis)')
                        ->schema([
                            Forms\Components\Repeater::make('operasional')
                                ->label('Daftar Kegiatan')
                                ->schema([
                                    Forms\Components\TextInput::make('kegiatan')
                                        ->label('Deskripsi Kegiatan')
                                        ->placeholder('Tulis kegiatan yang dilakukan...')
                                        ->required(),
                                    Forms\Components\Select::make('status')
                                        ->label('Status')
                                        ->options([
                                            'Selesai' => 'Selesai (Checklist)',
                                            'In Progress' => 'In Progress',
                                        ])
                                        ->required()
                                        ->default('Selesai'),
                                ])
                                ->default([
                                    ['kegiatan' => '', 'status' => 'Selesai']
                                ])
                                ->columnSpanFull()
                                ->createItemButtonLabel('Tambah Kegiatan'),
                        ]),

                    Section::make('Dokumentasi Kegiatan')
                        ->description('Tambahkan foto dan keterangan untuk setiap dokumentasi kegiatan')
                        ->schema([
                            Forms\Components\Repeater::make('dokumentasi')
                                ->label('Daftar Dokumentasi')
                                ->schema([
                                    Forms\Components\FileUpload::make('fotos')
                                        ->label('Foto Dokumentasi')
                                        ->disk('public')
                                        ->directory('laporan/dokumentasi')
                                        ->image()
                                        ->multiple()
                                        ->reorderable()
                                        ->required()
                                        ->columnSpanFull(),
                                    Forms\Components\Textarea::make('keterangan')
                                        ->label('Keterangan (Uraian Detail Kegiatan)')
                                        ->placeholder('Masukkan penjelasan detail mengenai kegiatan ini...')
                                        ->required()
                                        ->rows(4)
                                        ->columnSpanFull(),
                                ])
                                ->default([
                                    ['fotos' => [], 'keterangan' => '']
                                ])
                                ->columnSpanFull()
                                ->createItemButtonLabel('Tambah Dokumentasi'),
                        ]),
                ])->columnSpan(1),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_hari')
                    ->label('Hari')
                    ->getStateUsing(fn ($record) => $record->getHariIndonesian()),
                Tables\Columns\TextColumn::make('operasional')
                    ->label('Jumlah Kegiatan')
                    ->getStateUsing(fn ($record) => count($record->operasional ?? []) . ' kegiatan'),
                Tables\Columns\TextColumn::make('dokumentasi_count')
                    ->label('Jumlah Dokumentasi')
                    ->getStateUsing(fn ($record) => count($record->dokumentasi ?? []) . ' foto'),
            ])
            ->defaultSort('tanggal', 'desc')
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

    public static function getEloquentQuery(): Builder
    {
        // Karyawan hanya bisa melihat laporan miliknya sendiri
        return parent::getEloquentQuery()->where('user_id', Auth::id());
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListLaporanHarians::route('/'),
            'create' => Pages\CreateLaporanHarian::route('/create'),
            'edit'   => Pages\EditLaporanHarian::route('/{record}/edit'),
        ];
    }
}
