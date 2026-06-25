<?php

namespace App\Filament\Pegawai\Resources\LaporanHarianResource\Pages;

use App\Filament\Pegawai\Resources\LaporanHarianResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditLaporanHarian extends EditRecord
{
    protected static string $resource = LaporanHarianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterSave(): void
    {
        Notification::make()
            ->success()
            ->title('Laporan Berhasil Diperbarui')
            ->body('Perubahan pada laporan harian Anda telah disimpan.')
            ->send();
    }
}
