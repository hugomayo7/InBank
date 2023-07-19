<?php

namespace App\Filament\Widgets;

use App\Interfaces\PowensRepositoryInterface;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\Facades\App;

class BankAccountsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected static string $view = 'filament.widgets.bank-accounts-overview';

    protected function getColumns(): int
    {
        return 3;
    }

    protected function getTitle()
    {
        return 'Mes comptes';
    }

    protected function getCards(): array
    {
        $bankIcons = [
            'PayPal' => 'fab-paypal',
        ];

        $accounts = App::get(PowensRepositoryInterface::class)->getAccounts(auth()->user()->auth_token)->json()['accounts'] ?? [];

        return auth()->user()->bankAccounts->map(function ($bankAccount) use ($bankIcons, $accounts) {

            $icon = $bankIcons[$bankAccount->bank_name] ?? 'fas-building-columns';

            return Card::make($bankAccount->bank_name, 'N/A')
                ->value(function () use ($bankAccount, &$icon, $accounts) {

                    $balance = format_currency(collect($accounts)->where('id', $bankAccount->id)->first()['balance']);
                    $currency = collect($accounts)->where('id', $bankAccount->id)->first()['currency']['symbol'];

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
