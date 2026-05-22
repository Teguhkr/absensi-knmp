<?php

namespace App\Filament\Pegawai\Resources\IzinResource\Pages;

use App\Filament\Pegawai\Resources\IzinResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateIzin extends CreateRecord
{
    protected static string $resource = IzinResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        Notification::make()
            ->success()
            ->title('Pengajuan Berhasil')
            ->body('Pengajuan izin/sakit berhasil dikirim dan menunggu persetujuan.')
            ->send();
    }
}
