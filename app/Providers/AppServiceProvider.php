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
        Filament::registerScripts(['https://kit.fontawesome.com/29ab85e66a.js'], true);

        Filament::serving(function () {
            $powensDomainUrl = env('POWENS_DOMAIN_URL');
            $clientId = env('POWENS_CLIENT_ID');
            $redirectUri = env('POWENS_REDIRECT_URI');
            $auth_token = auth()->user()->auth_token ?? null;

            $url = $auth_token ? "$powensDomainUrl/auth/webview/connect?client_id=$clientId&redirect_uri=$redirectUri&code=$auth_token" : "$powensDomainUrl/auth/webview/connect?client_id=$clientId&redirect_uri=$redirectUri";

            Filament::registerUserMenuItems([
                UserMenuItem::make()
                    ->label('Connecter un compte')
                    ->url($url)
                    ->icon('heroicon-s-plus'),
            ]);
        });
    }
}
