<?php

namespace App\Filament\Pages;

use App\Models\PengaturanSistem;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class PengaturanSistemPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Pengaturan Sistem';
    protected static ?string $title = 'Pengaturan Sistem';
    protected static ?string $slug = 'pengaturan-sistem';
    protected static ?int $navigationSort = 5;
    
    protected string $view = 'filament.pages.pengaturan-sistem';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = PengaturanSistem::pluck('value', 'key')->toArray();
        $this->form->fill($settings);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Pengaturan Waktu Absensi')
                    ->description('Atur jam masuk, jam pulang, dan toleransi keterlambatan.')
                    ->schema([
                        TimePicker::make('jam_masuk')
                            ->label('Jam Masuk Kerja')
                            ->seconds(false)
                            ->required(),
                        TimePicker::make('jam_pulang')
                            ->label('Jam Pulang Kerja')
                            ->seconds(false)
                            ->required(),
                        TextInput::make('toleransi_menit')
                            ->label('Toleransi Keterlambatan (menit)')
                            ->numeric()
                            ->required(),
                    ])->columns(3),

                Section::make('Pengaturan Lokasi Kantor (GPS)')
                    ->description('Koordinat pusat kantor untuk validasi jarak absensi pegawai.')
                    ->schema([
                        Toggle::make('validasi_gps')
                            ->label('Aktifkan Validasi GPS saat Absen')
                            ->default(true)
                            ->columnSpanFull(),
                        TextInput::make('kantor_latitude')
                            ->label('Latitude Kantor')
                            ->numeric()
                            ->required(),
                        TextInput::make('kantor_longitude')
                            ->label('Longitude Kantor')
                            ->numeric()
                            ->required(),
                        TextInput::make('radius_absensi')
                            ->label('Radius Absensi (meter)')
                            ->numeric()
                            ->required()
                            ->helperText('Jarak maksimal pegawai bisa melakukan absen dari titik pusat kantor.'),
                    ])->columns(3),
                    
                Section::make('Informasi Instansi')
                    ->schema([
                        TextInput::make('nama_instansi')
                            ->label('Nama Instansi')
                            ->required()
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            PengaturanSistem::set($key, $value);
        }

        Notification::make()
            ->success()
            ->title('Pengaturan berhasil disimpan')
            ->send();
    }
}
