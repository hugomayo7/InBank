<?php

namespace App\Providers;

use Filament\Facades\Filament;
use Filament\Navigation\UserMenuItem;
use Illuminate\Support\ServiceProvider;

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
        Filament::registerViteTheme('resources/css/filament.css');

        Filament::serving(function () {
            $powensDomainUrl = env('POWENS_DOMAIN_URL');
            $clientId = env('POWENS_CLIENT_ID');
            $redirectUri = env('POWENS_REDIRECT_URI');

            Filament::registerUserMenuItems([
                UserMenuItem::make()
                    ->label('Connecter un compte')
                    ->url("$powensDomainUrl/auth/webview/connect?client_id=$clientId&redirect_uri=$redirectUri")
                    ->icon('heroicon-s-plus'),
            ]);
        });
    }
}
