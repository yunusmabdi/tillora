<?php

namespace App\Providers\Filament;

use App\Filament\Pages\RiderDashboard;
use App\Filament\Pages\RiderOrderDetails;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Filament\Pages\RiderDeliveryHistory;

class RiderPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('rider')
            ->path('rider')
            ->viteTheme('resources/css/filament/rider/theme.css')
            ->login()
            ->colors([
                'primary' => Color::Blue,
            ])

            ->discoverResources(
                in: app_path('Filament/Rider/Resources'),
                for: 'App\\Filament\\Rider\\Resources',
            )

            ->pages([
                RiderDashboard::class,
                RiderOrderDetails::class,
                RiderDeliveryHistory::class,
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