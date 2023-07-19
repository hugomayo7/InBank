<?php

namespace App\Filament\Widgets;

use App\Models\BankAccount;
use App\Repository\PowensRepository;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\Facades\Http;

class TotalBalanceOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '1000';

    protected function getCards(): array
    {
        $powensDomainUrl = env('POWENS_DOMAIN_URL');

        return [
            Card::make('Solde Total', 'N/A')
                ->value(function () use ($powensDomainUrl) {
                    $total = 0;
                    $powensRepository = new PowensRepository();

                    // todo : refactor ? => call all banks with one request
                    foreach (BankAccount::all() as $bankAccount) {
                        $request = Http::withToken($bankAccount->auth_token)
                            ->get("$powensDomainUrl/users/me/accounts")
                            ->json();

                        $bank = collect($request['accounts'])->where('id', $bankAccount->id)->first();

                        if (collect($request['accounts'])->where('id', $bankAccount->id)->first()['currency']['id'] != 'EUR') {
                            $currency = $bank['currency']['id'];
                            $balance = $bank['balance'];

                            $convertedResult = convert_currency($balance, $currency, 'EUR');

                            $total += $convertedResult;
                        } else {
                            $total += $bank['balance'];
                        }
                    }

                    return round($total, 2) . ' €' ?? 'N/A';
                })
                ->description('Solde total de tous les comptes')
                ->icon('fas-money-check-dollar'),
        ];
    }
}
