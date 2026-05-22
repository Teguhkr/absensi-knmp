<?php

namespace App\Filament\Pegawai\Resources\IzinResource\Pages;

use App\Filament\Pegawai\Resources\IzinResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIzins extends ListRecords
{
    protected static string $resource = IzinResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Ajukan Izin / Sakit'),
        ];
    }
}
