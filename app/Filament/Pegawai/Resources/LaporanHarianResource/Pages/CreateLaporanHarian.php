<?php

namespace App\Filament\Pegawai\Resources\LaporanHarianResource\Pages;

use App\Filament\Pegawai\Resources\LaporanHarianResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateLaporanHarian extends CreateRecord
{
    protected static string $resource = LaporanHarianResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        Notification::make()
            ->success()
            ->title('Laporan Berhasil Disimpan')
            ->body('Laporan harian Anda telah berhasil dibuat.')
            ->send();
    }
}
