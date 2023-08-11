<?php

namespace App\Filament\Resources\BankAccountResource\Pages;

use App\Filament\Resources\BankAccountResource;
use App\Interfaces\PowensRepositoryInterface;
use App\Models\BankAccount;
use App\Models\Transaction;
use Closure;
use Filament\Notifications\Notification;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\App;

class ListBankAccounts extends ListRecords
{
    protected static string $resource = BankAccountResource::class;

    public function mount(): void
    {
        parent::mount();
        App::get(PowensRepositoryInterface::class)->authenticate(auth()->user(), request());

        $auth_token = auth()->user()->auth_token;

        if (request()->has('connection_id')) {
            $connection_id = request()->get('connection_id');
            $accounts_request = App::get(PowensRepositoryInterface::class)->getConnectionAccounts($auth_token, $connection_id);

            if ($accounts_request->successful()) {
                $results = $accounts_request->json();

                foreach ($results['accounts'] as $account) {
                    if ($account['type'] != 'savings') {
                        $transactions = [];

                        while (empty($transactions)) {
                            $transactions = App::get(PowensRepositoryInterface::class)->getAccountTransactions($auth_token, $account['id']);
                        }

                        foreach ($transactions as $transaction) {
                            $bankAccount = BankAccount::where('account_id', $transaction['id_account'])->first();

                            if ($bankAccount && $bankAccount->transactions()->count() === 0) {
                                $type = null;
                                switch ($transaction['type']) {
                                    case 'card':
                                        $type = Transaction::CARD_TYPE;
                                        break;
                                    case 'transfer':
                                        $type = Transaction::TRANSFER_TYPE;
                                        break;
                                    case 'order':
                                        $type = Transaction::ORDER_TYPE;
                                        break;
                                    case 'payback':
                                        $type = Transaction::PAYBACK_TYPE;
                                        break;
                                    case 'withdrawal':
                                        $type = Transaction::WITHDRAWAL_TYPE;
                                        break;
                                    case 'bank':
                                        $type = Transaction::BANK_TYPE;
                                        break;
                                    default:
                                        $type = Transaction::UNKNOWN_TYPE;
                                        break;
                                }

                                Transaction::updateOrInsert([
                                    'transaction_id' => $transaction['id'],
                                ],[
                                    'transaction_id' => $transaction['id'],
                                    'bank_account_id' => $bankAccount->id,
                                    'value' => $transaction['value'] * 100,
                                    'original_wording' => $transaction['original_wording'],
                                    'simplified_wording' => $transaction['simplified_wording'],
                                    'stemmed_wording' => $transaction['stemmed_wording'],
                                    'wording' => $transaction['wording'],
                                    'type' => $type,
                                    'application_date' => $transaction['application_date'],
                                ]);
                            }
                        }
                    }
                }
            } else {
                Notification::make()
                    ->danger()
                    ->title('Erreur')
                    ->body('Ce compte n\'existe pas ou n\'est pas accessible')
                    ->send();
            }

        }
    }

    protected function getActions(): array
    {
        $powensDomainUrl = env('POWENS_DOMAIN_URL');
        $clientId = env('POWENS_CLIENT_ID');
        $redirectUri = env('POWENS_REDIRECT_URI');

        $auth_token = auth()->user()->auth_token;
        $url = $auth_token ? "$powensDomainUrl/auth/webview/connect?client_id=$clientId&redirect_uri=$redirectUri&code=$auth_token" : "$powensDomainUrl/auth/webview/connect?client_id=$clientId&redirect_uri=$redirectUri";


        return [
            Action::make('add_account')
                ->url($url)
                ->label('Ajouter un compte')
                ->icon('heroicon-o-plus-circle'),

            Action::make('manage_account')
                ->url("$powensDomainUrl/auth/webview/fr/manage?client_id=$clientId&redirect_uri=$redirectUri&code=$auth_token")
                ->label('Gérer mes comptes')
                ->disabled(!$auth_token)
                ->hidden(!$auth_token)
                ->icon('heroicon-o-cog'),
        ];
    }

    protected function getTableRecordUrlUsing(): ?Closure
    {
        return null;
    }
}
