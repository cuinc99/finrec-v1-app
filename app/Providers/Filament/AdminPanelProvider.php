<?php

namespace App\Providers\Filament;

use Filament\Panel;
use Filament\Widgets;
use Filament\PanelProvider;
use Filament\Enums\ThemeMode;
use App\Filament\Pages\Dashboard;
use App\Filament\Pages\Auth\Login;
use Filament\Support\Colors\Color;
use App\Filament\Pages\Auth\EditProfile;
use App\Filament\Pages\Settings\Backups;
use Filament\Navigation\NavigationGroup;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use TomatoPHP\FilamentNotes\FilamentNotesPlugin;
use Filament\Http\Middleware\AuthenticateSession;
use Awcodes\FilamentQuickCreate\QuickCreatePlugin;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use TomatoPHP\FilamentNotes\Filament\Widgets\NotesWidget;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use ShuvroRoy\FilamentSpatieLaravelBackup\FilamentSpatieLaravelBackupPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class)
            ->colors([
                'primary' => Color::Pink,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
                NotesWidget::class
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
            ])
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->defaultThemeMode(ThemeMode::Light)
            ->profile(EditProfile::class)
            ->spa()
            // ->topNavigation(true)
            ->breadcrumbs(false)
            ->sidebarCollapsibleOnDesktop()
            ->defaultThemeMode(ThemeMode::Light)
            ->unsavedChangesAlerts()
            // ->databaseNotifications()
            ->navigationGroups([
                NavigationGroup::make()
                    ->label(__('models.navigation_group.master_data')),
                NavigationGroup::make()
                    ->label(__('models.navigation_group.content')),
                NavigationGroup::make()
                    ->label(__('models.navigation_group.setting')),
            ])
            ->plugins([
                FilamentSpatieLaravelBackupPlugin::make()
                    ->usingPage(Backups::class),
                QuickCreatePlugin::make()
                    ->excludes([
                        \App\Filament\Resources\UserResource::class,
                    ])
                    ->createAnother(false),
                FilamentNotesPlugin::make()
                    ->useUserAccess()
                    ->useChecklist()
                    ->navigationIcon('heroicon-m-bookmark')
            ]);
    }
}
