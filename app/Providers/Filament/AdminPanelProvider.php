<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\AbsensiTerbaruWidget;
use App\Filament\Widgets\DashboardHeaderWidget;
use App\Filament\Widgets\GrafikKehadiranWidget;
use App\Filament\Widgets\RasioKehadiranHariIniWidget;
use App\Filament\Widgets\StatistikHariIniWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::hex('#0B3B60'),
            ])
            ->brandName('KNMP | Panel Admin')
            ->brandLogo(asset('logo-knmp.png'))
            ->brandLogoHeight('2.5rem')
            ->favicon(asset('logo-knmp.png'))
            ->font('Plus Jakarta Sans')
            ->sidebarCollapsibleOnDesktop()
            ->renderHook(
                \Filament\View\PanelsRenderHook::HEAD_END,
                fn () => new \Illuminate\Support\HtmlString('
                    <link rel="stylesheet" href="' . asset('css/custom-filament.css') . '">
                ')
            )
            ->profile(\App\Filament\Pages\Auth\EditProfile::class)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                DashboardHeaderWidget::class,
                StatistikHariIniWidget::class,
                GrafikKehadiranWidget::class,
                RasioKehadiranHariIniWidget::class,
                AbsensiTerbaruWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
