<?php

namespace App\Filament\Resources\LaporanHarianResource\Pages;

use App\Filament\Resources\LaporanHarianResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLaporanHarian extends CreateRecord
{
    protected static string $resource = LaporanHarianResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
