<?php

namespace App\Providers\Filament;

use Filament\Pages\Dashboard;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
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
            ->login()
            ->brandName('LabKes Sragen')
            ->sidebarCollapsibleOnDesktop(true)
            ->navigationGroups([
                'Operasional',
                'Keuangan',
                'Master Data',
                'Laporan',
                'Pengaturan',
            ])
            ->maxContentWidth('85%')
            ->colors([
                'primary' => Color::Blue,
                'gray' => Color::Slate, // Slate is more "Enterprise" than Zinc
                'info' => Color::Blue,
                'success' => Color::Emerald, // Soft green
                'warning' => Color::Orange, // Soft orange
                'danger' => Color::Rose, // Soft red
            ])
            ->renderHook(
                'panels::head.end',
                fn () => \Illuminate\Support\Facades\Blade::render('@vite("resources/css/app.css")')
            )
            ->renderHook(
                'panels::body.end',
                fn () => view('filament.hooks.cbox-js')
            )
            ->font('Inter')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
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
