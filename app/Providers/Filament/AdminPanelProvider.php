<?php

namespace App\Providers\Filament;

use App\Filament\Resources\CustomerResource\Widgets\CustomerLineChart;
use BezhanSalleh\FilamentLanguageSwitch\FilamentLanguageSwitchPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    /**
     * @throws \Exception
     */
    public function panel(Panel $panel): Panel
    {

        return $panel
            ->default()
            ->id('admin')
            ->databaseNotifications()
            ->globalSearch(true)
            ->path('admin')
            ->login()

            ->colors([
                'primary' => '#282361',
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->profile()
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([

                CustomerLineChart::class,
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
            ])->navigationGroups([
                NavigationGroup::make()
                    ->label(fn () => __('Main Settings'))
                    ->icon('heroicon-o-cog-6-tooth'),
                NavigationGroup::make()
                    ->label(fn () => __('Users'))
                    ->icon('heroicon-o-user'),
                NavigationGroup::make()
                    ->label(fn () => __('Appointments'))
                    ->icon('heroicon-o-calendar'),
                NavigationGroup::make('Marketing')
                    ->label(fn () => __('Marketing'))
                    ->icon('heroicon-o-newspaper'),
                NavigationGroup::make('Sales')
                    ->label(fn () => __('Sales'))
                    ->icon('heroicon-o-receipt-percent'),
                NavigationGroup::make('Subscriptions')
                    ->label(fn () => __('Subscriptions'))
                    ->icon('heroicon-o-ticket'),
                NavigationGroup::make('Gift Cards')
                    ->label(fn () => __('Gift Cards'))
                    ->icon('heroicon-o-gift'),
                NavigationGroup::make('Loyalty Points')
                    ->label(fn () => __('Loyalty Points'))
                    ->icon('heroicon-o-currency-dollar'),
                NavigationGroup::make('Financial')
                    ->label(fn () => __('Financial'))
                    ->icon('heroicon-o-banknotes'),
                NavigationGroup::make('Settings')
                    ->label(fn () => __('Settings'))
                    ->icon('heroicon-o-cog-6-tooth'),

                    ])->plugins([FilamentLanguageSwitchPlugin::make()->renderHookName('panels::user-menu.after'),
            ]);

    }
}
