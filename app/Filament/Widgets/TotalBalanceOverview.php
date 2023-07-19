<?php

namespace App\Filament\Widgets;

use App\Interfaces\PowensRepositoryInterface;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\Facades\App;

class TotalBalanceOverview extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getCards(): array
    {
        return [
            Card::make('Solde Total', 'N/A')
                ->value(function () {
                    return App::get(PowensRepositoryInterface::class)->getTotalBalance(auth()->user()->auth_token);
                })
                ->description('Solde total de tous les comptes')
                ->icon('fas-money-check-dollar'),
        ];
    }
}
