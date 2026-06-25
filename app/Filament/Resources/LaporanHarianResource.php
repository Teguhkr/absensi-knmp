<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LaporanHarianResource\Pages;
use App\Models\LaporanHarian;
use App\Models\User;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Group;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LaporanHarianResource extends Resource
{
    protected static ?string $model = LaporanHarian::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Laporan Harian';
    protected static ?string $modelLabel = 'Laporan Harian';
    protected static ?string $pluralModelLabel = 'Laporan Harian';
    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Group::make([
                    Section::make('Informasi Laporan')
                        ->schema([
                            Forms\Components\Select::make('user_id')
                                ->label('Pegawai')
                                ->relationship('user', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),
                            Forms\Components\DatePicker::make('tanggal')
                                ->label('Tanggal Laporan')
                                ->required()
                                ->minDate(fn ($record) => $record ? null : now()->startOfMonth())
                                ->maxDate(fn ($record) => $record ? null : now())
                                ->rules(function ($record, $get) {
                                    $rule = \Illuminate\Validation\Rule::unique('laporan_harians', 'tanggal')
                                        ->where('user_id', $get('user_id'));
                                    if ($record) {
                                        $rule->ignore($record->id);
                                    }
                                    return [$rule];
                                })
                                ->validationMessages([
                                    'unique' => 'Laporan untuk pegawai dan tanggal ini sudah ada.',
                                ])
                                ->default(now()),
                        ])->columns(2),

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
                        ->description('Kegiatan operasional harian yang dilaksanakan')
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
                        ->schema([
                            Forms\Components\FileUpload::make('dokumentasi')
                                ->label('Foto Dokumentasi')
                                ->disk('public')
                                ->directory('laporan/dokumentasi')
                                ->image()
                                ->required()
                                ->columnSpanFull(),
                            Forms\Components\Textarea::make('keterangan_dokumentasi')
                                ->label('Keterangan Dokumentasi (Uraian Detail Kegiatan)')
                                ->placeholder('Masukkan penjelasan detail mengenai kegiatan hari ini...')
                                ->required()
                                ->rows(6)
                                ->columnSpanFull(),
                        ]),
                ])->columnSpan(1),
            ])
            ->columns(2);
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
                    ->badge()
                    ->color('info')
                    ->searchable(),
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
                Tables\Columns\TextColumn::make('keterangan_dokumentasi')
                    ->label('Keterangan')
                    ->limit(40),
            ])
            ->defaultSort('tanggal', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Pegawai')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('departemen')
                    ->label('Departemen')
                    ->options(fn () => User::whereNotNull('departemen')->pluck('departemen', 'departemen')->toArray())
                    ->query(function (Builder $query, array $data) {
                        if (empty($data['value'])) {
                            return $query;
                        }
                        return $query->whereHas('user', fn ($q) => $q->where('departemen', $data['value']));
                    }),
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
            'index'  => Pages\ListLaporanHarians::route('/'),
            'create' => Pages\CreateLaporanHarian::route('/create'),
            'edit'   => Pages\EditLaporanHarian::route('/{record}/edit'),
        ];
    }
}
