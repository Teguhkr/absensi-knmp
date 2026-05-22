<?php

namespace App\Filament\Pegawai\Resources;

use App\Filament\Pegawai\Resources\PengumumanResource\Pages;
use App\Models\Pengumuman;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class PengumumanResource extends Resource
{
    protected static ?string $model = Pengumuman::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-megaphone';
    protected static ?string $navigationLabel = 'Pengumuman';
    protected static ?string $modelLabel = 'Pengumuman';
    protected static ?string $pluralModelLabel = 'Pengumuman';
    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Detail Pengumuman')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('judul')
                            ->label('Judul')
                            ->disabled()
                            ->columnSpanFull(),
                        \Filament\Forms\Components\Placeholder::make('created_at')
                            ->label('Tanggal Pengumuman')
                            ->content(fn ($record) => $record?->created_at?->format('d M Y H:i') ?? '-'),
                        \Filament\Forms\Components\Placeholder::make('created_by')
                            ->label('Dibuat Oleh')
                            ->content(fn ($record) => $record?->creator?->name ?? '-'),
                        \Filament\Forms\Components\Placeholder::make('isi')
                            ->label('Isi Pengumuman')
                            ->columnSpanFull()
                            ->content(fn ($record) => new HtmlString($record?->isi ?? '')),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('judul')
                    ->label('Judul Pengumuman')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(60),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Dibuat Oleh')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                \Filament\Actions\ViewAction::make()
                    ->modalWidth('2xl')
                    ->slideOver(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('is_active', true);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListListPengumuman::route('/'),
        ];
    }
}
