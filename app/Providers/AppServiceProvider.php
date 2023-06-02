<?php

namespace App\Providers;

use App\Models\Notion;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;
use Filament\Navigation\UserMenuItem;
use Illuminate\Support\ServiceProvider;
use Filament\Navigation\NavigationGroup;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            \URL::forceScheme('https');
        }
        Filament::serving(function () {
            Filament::registerNavigationGroups([
                NavigationGroup::make()
                     ->label('Notion'),
                NavigationGroup::make()
                     ->label('Notion Collaborator'),
                NavigationGroup::make()
                     ->label('Team'),
                NavigationGroup::make()
                    ->label('Account')
            ]);

            Filament::registerViteTheme('resources/css/app.css');
        });
    }
}