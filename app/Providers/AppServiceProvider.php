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
        Filament::serving(function () {
            // if (Auth::check()) {
            //     $user = Auth::user();
            //     $notionToken = Notion::where('user_id', $user->id)->first();
    
            //     if ($notionToken) {
            //         $label = 'Notion Already Signed In';
            //         $url = null;
            //         $color = 'success';
            //     } else {
            //         $label = 'Sign in Notion';
            //         $url = route('login.notion');
            //         $color = 'danger';
            //     }
            //     Filament::registerUserMenuItems([
            //         UserMenuItem::make()
            //             ->label($label)
            //             ->url($url)
            //             ->icon('heroicon-s-lightning-bolt')
            //             ->color($color),
            //     ]);
            // }
            Filament::registerNavigationGroups([
                NavigationGroup::make()
                     ->label('Notion'),
                NavigationGroup::make()
                    ->label('Account')
            ]);

            Filament::registerViteTheme('resources/css/app.css');
        });
    }
}