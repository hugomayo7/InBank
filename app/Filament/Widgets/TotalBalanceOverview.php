<?php

namespace App\Filament\Widgets;

use App\Interfaces\PowensRepositoryInterface;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\App;

class TotalBalanceOverview extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 1;

    public $balance = 'N/A';
    public function fetchBalance()
    {
        $this->balance = App::get(PowensRepositoryInterface::class)->getTotalBalance(auth()->user()->auth_token);
    }

    protected function getCards(): array
    {
        return [
            Card::make('Solde Total', 'N/A')
                ->value(function () {
                    return $this->balance;
                })
                ->description('Solde total de tous les comptes')
                ->icon('fas-money-check-dollar'),
        ];
    }

    public function render(): View
    {
        return view('filament.widgets.total-balance-overview');
    }
}
