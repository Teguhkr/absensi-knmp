<?php

namespace App\Filament\Pegawai\Widgets;

use App\Models\Pengumuman;
use Filament\Widgets\Widget;

class PengumumanTerbaruWidget extends Widget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    protected string $view = 'filament.pegawai.widgets.pengumuman-terbaru-widget';

    public function getPengumuman()
    {
        return Pengumuman::where('is_active', true)
            ->with('creator')
            ->latest()
            ->get();
    }
}
