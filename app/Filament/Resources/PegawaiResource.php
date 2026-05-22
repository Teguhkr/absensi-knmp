<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PegawaiResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class PegawaiResource extends Resource
{
    protected static ?string $model = User::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Data Pegawai';
    protected static ?string $modelLabel = 'Pegawai';
    protected static ?string $pluralModelLabel = 'Data Pegawai';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Informasi Akun')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nama Lengkap')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('password')
                        ->label('Password')
                        ->password()
                        ->dehydrateStateUsing(fn ($state) => !empty($state) ? bcrypt($state) : null)
                        ->dehydrated(fn ($state) => !empty($state))
                        ->required(fn (string $context) => $context === 'create')
                        ->placeholder('Kosongkan jika tidak diubah'),
                ])->columns(2),

            Section::make('Informasi Pegawai')
                ->schema([
                    Forms\Components\TextInput::make('nip')
                        ->label('NIP')
                        ->unique(ignoreRecord: true)
                        ->maxLength(50),
                    Forms\Components\Select::make('role')
                        ->label('Role')
                        ->options([
                            'admin'   => 'Admin',
                            'pegawai' => 'Pegawai',
                        ])
                        ->default('pegawai')
                        ->required(),
                    Forms\Components\TextInput::make('jabatan')
                        ->label('Jabatan')
                        ->maxLength(255),
                    Forms\Components\TextInput::make('departemen')
                        ->label('Departemen / Bidang')
                        ->maxLength(255),
                    Forms\Components\TextInput::make('no_hp')
                        ->label('Nomor HP')
                        ->tel()
                        ->maxLength(20),
                    Forms\Components\Toggle::make('is_active')
                        ->label('Status Aktif')
                        ->default(true),
                ])->columns(2),

            Section::make('Foto Profil')
                ->schema([
                    Forms\Components\FileUpload::make('foto')
                        ->label('Foto Pegawai')
                        ->image()
                        ->disk('public')
                        ->directory('pegawai/foto')
                        ->imageResizeMode('cover')
                        ->imageCropAspectRatio('1:1')
                        ->imageResizeTargetWidth(300)
                        ->imageResizeTargetHeight(300)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('foto')
                    ->label('Foto')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&background=0ea5e9&color=fff'),
                Tables\Columns\TextColumn::make('nip')
                    ->label('NIP')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('jabatan')
                    ->label('Jabatan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('departemen')
                    ->label('Departemen')
                    ->searchable()
                    ->badge()
                    ->color('info'),
                Tables\Columns\BadgeColumn::make('role')
                    ->label('Role')
                    ->colors([
                        'danger'  => 'admin',
                        'success' => 'pegawai',
                    ]),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options([
                        'admin'   => 'Admin',
                        'pegawai' => 'Pegawai',
                    ]),
                Tables\Filters\SelectFilter::make('departemen')
                    ->options(fn () => User::whereNotNull('departemen')->pluck('departemen', 'departemen')->toArray())
                    ->label('Departemen'),
                Tables\Filters\TernaryFilter::make('is_active')->label('Status Aktif'),
            ])
            ->actions([
                \Filament\Actions\Action::make('qr_code')
                    ->label('QR Code')
                    ->icon('heroicon-o-qr-code')
                    ->color('success')
                    ->url(fn (User $record) => route('absensi.scan', $record->qr_token))
                    ->openUrlInNewTab(),
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPegawai::route('/'),
            'create' => Pages\CreatePegawai::route('/create'),
            'edit'   => Pages\EditPegawai::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->where('role', '!=', null);
    }
}
