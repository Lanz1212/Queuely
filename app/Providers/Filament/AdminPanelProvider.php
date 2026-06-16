<?php

namespace App\Providers\Filament;

use App\Http\Middleware\ApplySessionSettings;
use App\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use App\Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Filament\Navigation\NavigationItem;
use Filament\Navigation\NavigationGroup;
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
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->login(false)
            ->colors([
                'primary' => Color::Blue,
            ])
            ->brandName('Queuely')
            ->navigationItems([
                NavigationItem::make('Ruang Tunggu')
                    ->url(fn (): string => route('display'))
                    ->icon('heroicon-o-tv')
                    ->group('Display & Registrasi')
                    ->openUrlInNewTab()
                    ->visible(fn (): bool => auth()->user()?->isAdmin() ?? false)
                    ->sort(1),
                NavigationItem::make('Cetak Antrian')
                    ->url(fn (): string => route('kiosk'))
                    ->icon('heroicon-o-qr-code')
                    ->group('Display & Registrasi')
                    ->openUrlInNewTab()
                    ->visible(fn (): bool => auth()->user()?->isAdmin() ?? false)
                    ->sort(2),
            ])
            ->navigationGroups([
                NavigationGroup::make('Loket'),
                NavigationGroup::make('Pengaturan'),
                NavigationGroup::make('Display & Registrasi'),
            ])
            ->renderHook(
                'panels::body.end',
                fn () => view('components.admin-audio-player'),
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                ApplySessionSettings::class,
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
