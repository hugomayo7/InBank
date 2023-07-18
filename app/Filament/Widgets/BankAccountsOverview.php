<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\Facades\Http;

class BankAccountsOverview extends BaseWidget
{
    protected function getColumns(): int
    {
        return 3;
    }

    protected function getCards(): array
    {
        $powensDomainUrl = env('POWENS_DOMAIN_URL');

        $bankIcons = [
            'PayPal' => 'fab-paypal',
        ];

        return auth()->user()->bankAccounts->map(function ($bankAccount) use ($powensDomainUrl, $bankIcons) {

            $icon = $bankIcons[$bankAccount->bank_name] ?? 'fas-building-columns';

            return Card::make($bankAccount->bank_name, 'N/A')
                ->value(function () use ($bankAccount, $powensDomainUrl, &$icon) {

                    $request = Http::withToken($bankAccount->auth_token)
                        ->get("$powensDomainUrl/users/me/accounts")
                        ->json();

                    $balance = collect($request['accounts'])->where('id', $bankAccount->id)->first()['balance'];
                    $currency = collect($request['accounts'])->where('id', $bankAccount->id)->first()['currency']['symbol'];

                    switch ($bankAccount->bank_name) {
                        case 'PayPal':
                            $icon = 'fab-paypal';
                            break;
                        default:
                            $icon = 'fas-building-columns';
                            break;
                    }

                    return "$balance $currency" ?? 'N/A';
                })
                ->description($bankAccount->original_name)->icon($icon);
        })->toArray();
    }
}
