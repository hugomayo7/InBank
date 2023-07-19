<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\BankAccountsOverview;
use App\Filament\Widgets\TotalBalanceOverview;
use App\Interfaces\PowensRepositoryInterface;
use Filament\Pages\Dashboard as BasePage;
use Illuminate\Support\Facades\App;

class Dashboard extends BasePage
{
    protected static string $view = 'filament.pages.dashboard';
    protected static ?string $navigationIcon = 'fas-home';

    public static $icon = 'heroicon-s-home';

    public function mount()
    {
        App::get(PowensRepositoryInterface::class)->authenticate(auth()->user(), request());
    }

    protected function getColumns(): int|string|array
    {
        return 1;
    }

    protected function getWidgets(): array
    {
        return [
            TotalBalanceOverview::class,
            BankAccountsOverview::class
        ];
    }

    protected static function getNavigationBadge(): ?string
    {
        return App::get(PowensRepositoryInterface::class)->getTotalBalance(auth()->user()->auth_token);
    }
}
