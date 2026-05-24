<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class DashboardHeaderWidget extends Widget
{
    protected static ?int $sort = 0;
    protected int | string | array $columnSpan = 'full';

    protected string $view = 'filament.widgets.dashboard-header-widget';
}
