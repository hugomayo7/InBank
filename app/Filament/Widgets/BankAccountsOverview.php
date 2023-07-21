<?php

namespace App\Filament\Widgets;

use App\Interfaces\PowensRepositoryInterface;
use Filament\Notifications\Notification;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use \App\Filament\Widgets\BankAccountList\Card;
use Illuminate\Support\Facades\App;

class BankAccountsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected static string $view = 'filament.widgets.bank-accounts-overview';

    protected $accountsData = [];

    public function fetchAccountsData()
    {
        $accountsData = App::get(PowensRepositoryInterface::class)->getAccounts(auth()->user()->auth_token)->json()['accounts'] ?? [];

        $this->accountsData = collect($accountsData)->keyBy('id')->toArray();
    }

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

        return auth()->user()->bankAccounts->map(function ($bankAccount) use ($bankIcons) {
            $icon = $bankIcons[$bankAccount->bank_name] ?? 'fas-building-columns';

            return Card::make($bankAccount->bank_name, 'N/A')
                ->value(function () use ($bankAccount, &$icon) {
                    // Vérifiez si les données sont disponibles dans $this->accountsData
                    if (isset($this->accountsData[$bankAccount->id])) {
                        $accountData = $this->accountsData[$bankAccount->id];
                        $balance = format_currency($accountData['balance']);
                        $currency = $accountData['currency']['symbol'];

                        switch ($bankAccount->bank_name) {
                            case 'PayPal':
                                $icon = 'fab-paypal';
                                break;
                            default:
                                $icon = 'fas-building-columns';
                                break;
                        }

                        return "$balance $currency";
                    } else {
                        return 'N/A';
                    }
                })
                ->iban($bankAccount->iban ?? null)
                ->accountId($bankAccount->account_id)
                ->description($bankAccount->original_name)
                ->icon($icon)
                ->extraAttributes([
                    'wire:key' => $bankAccount->id,
                ]);
        })->toArray();
    }
}
