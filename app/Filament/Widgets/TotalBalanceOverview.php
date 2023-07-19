<?php

namespace App\Filament\Widgets;

use App\Interfaces\PowensRepositoryInterface;
use App\Models\BankAccount;
use App\Repository\PowensRepository;
use Filament\Notifications\Notification;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;

class TotalBalanceOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '1000';

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
