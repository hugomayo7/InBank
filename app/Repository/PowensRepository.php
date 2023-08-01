<?php

namespace App\Repository;

use App\Interfaces\PowensRepositoryInterface;
use App\Models\BankAccount;
use App\Models\Transaction;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PowensRepository implements PowensRepositoryInterface
{
    private $api_url;

    public function __construct()
    {
        $this->api_url = env('POWENS_DOMAIN_URL');
    }

    public function authenticate(?Authenticatable $user, Request $request)
    {
        if (request()->has('code') || request()->has('connection_id')) {
            $auth_token = null;

            if (!$user->auth_token) {
                $code = request()->get('code');

                $auth_request = Http::post("$this->api_url/auth/token/access", [
                    'client_id' => env('POWENS_CLIENT_ID'),
                    'client_secret' => env('POWENS_CLIENT_SECRET'),
                    'code' => $code,
                ]);

                if ($auth_request->successful()) {
                    $user->update(['auth_token' => $auth_request->json()['access_token']]);
                }
            }

            $auth_token = $user->auth_token;

            if (request()->has('connection_id')) {
                $connection_id = request()->get('connection_id');
                $accounts_request = $this->getConnectionAccounts($auth_token, $connection_id);
            } else {
                $accounts_request = $this->getAccounts($auth_token);
            }

            if ($accounts_request->successful()) {

                $results = $accounts_request->json();

                foreach ($results['accounts'] as $account) {
                    $bankAccount = BankAccount::updateOrCreate(
                        [
                            'original_name' => $account['original_name'],
                        ], [
                        'id' => $account['id'],
                        'user_id' => $user->id,
                        'connector_uuid' => $account['connection']['connector']['uuid'],
                        'bank_name' => $account['connection']['connector']['name'],
                        'iban' => $account['iban'],
                        'account_id' => $account['id'],
                        'last_updated_at' => $account['last_update'],
                        'original_name' => $account['original_name'],
                    ]);

                    if ($bankAccount->connection_id == null) {

                        $bankAccount->update(['connection_id' => $account['id_connection']]);

                        Notification::make('account_created')
                            ->success()
                            ->title('Authentification réussie')
                            ->body('Le compte a été ajouté.')
                            ->send();
                    } else {
                        Notification::make('account_updated')
                            ->success()
                            ->title('Compte déjà existant')
                            ->body('Le compte a été mis à jour.')
                            ->send();
                    }
                }
            } else {
                Notification::make('auth_error')
                    ->danger()
                    ->title('Authentification échouée')
                    ->body('Une erreur est survenue lors de la récupération de vos données bancaires.');
            }
        }
    }

    public function getAccounts($auth_token)
    {
        return Http::withToken($auth_token)->get("$this->api_url/users/me/accounts?expand=connection[connector]");
    }

    public function getAccount($auth_token, $account_id)
    {
        return Http::withToken($auth_token)->get("$this->api_url/users/me/accounts/$account_id?expand=connection[connector]");
    }

    public function getConnectionAccounts($auth_token, $connection_id)
    {
        return Http::withToken($auth_token)->get("$this->api_url/users/me/connections/$connection_id/accounts?expand=connection[connector]");
    }

    public function getTotalBalance($auth_token)
    {
        $total = 0;
        $request = $this->getAccounts($auth_token);
        $bankAccounts = [];

        if ($request->successful()) {
            $bankAccounts = $request->json()['accounts'];

            foreach ($bankAccounts as $bankAccount) {

                if ($bankAccount['currency']['id'] != 'EUR') {
                    $currency = $bankAccount['currency']['id'];
                    $balance = $bankAccount['balance'];

                    $convertedResult = convert_currency($balance, $currency, 'EUR');

                    $total += $convertedResult;
                } else {
                    $total += $bankAccount['balance'];
                }
            }
        } else if (auth()->user()->auth_token) {
            Notification::make('auth_error')
                ->title('Erreur')
                ->body('Une erreur est survenue lors de la récupération des comptes bancaires')
                ->send();
        }

        return format_currency($total) . ' €' ?? '0';
    }

    public function deleteConnection($auth_token, $connection_id)
    {
        $request = Http::withToken($auth_token)->delete("$this->api_url/users/me/connections/$connection_id");

        if ($request->successful()) {
            $bankAccounts = BankAccount::where('connection_id', $connection_id)->get();

            foreach ($bankAccounts as $bankAccount) {
                $bankAccount->delete();
            }

            Notification::make('connection_deleted')
                ->success()
                ->title('Connexion supprimée')
                ->body('La connexion a été supprimée avec succès.')
                ->send();
        } else {
            Notification::make('connection_deleted')
                ->danger()
                ->title('Erreur')
                ->body('Une erreur est survenue lors de la suppression de la connexion.')
                ->send();
        }
    }

    public function deleteConnections($auth_token, $connection_ids)
    {
        $ids = array_unique($connection_ids);
        foreach ($ids as $connection_id) {
            $this->deleteConnection($auth_token, $connection_id);
        }
    }

    public function updateConnection($auth_token, $connection_id)
    {
        $request = Http::withToken($auth_token)->put("$this->api_url/users/me/connections/$connection_id");

        if ($request->successful()) {
            $bankAccounts = BankAccount::where('connection_id', $connection_id)->get();

            foreach ($bankAccounts as $bankAccount) {
                $bankAccount->update(['last_updated_at' => $request->json()['last_update']]);
            }

            Notification::make('connection_updated')
                ->success()
                ->title('Données mise à jour')
                ->body('Les données ont été mises à jour avec succès.')
                ->send();
        } else {
//            Notification::make('connection_updated_error')
//                ->danger()
//                ->title('Erreur')
//                ->body('Une erreur est survenue lors de la mise à jour des données.')
//                ->send();
        }
    }

    public function updateConnections($auth_token, $connection_ids)
    {
        $ids = array_unique($connection_ids);
        foreach ($ids as $connection_id) {
            $this->updateConnection($auth_token, $connection_id);
        }
    }

    public function refreshData()
    {
        $connection_ids_to_update = array_unique(BankAccount::all()->pluck('connection_id')->toArray());

        $this->updateConnections(auth()->user()->auth_token, $connection_ids_to_update);
        $this->fetchAllTransactions(auth()->user()->auth_token);
    }

    public function fetchAllTransactions($auth_token)
    {
        $last_transaction_date = Transaction::orderBy('application_date', 'desc')->first()->application_date ?? null;

        if (!$last_transaction_date) {
            $request = Http::withToken($auth_token)->get("$this->api_url/users/me/transactions");
        } else {
            $last_transaction_date = Carbon::parse($last_transaction_date)->addDay()->format('Y-m-d');
            $request = Http::withToken($auth_token)->get("$this->api_url/users/me/transactions?min_date=$last_transaction_date");
        }

        if ($request->successful()) {
            $transactions = $request->json()['transactions'];
            $insert_transactions = [];

            foreach ($transactions as $transaction) {
                $bankAccount = BankAccount::where('account_id', $transaction['id_account'])->first();

                if ($bankAccount) {
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

                    $insert_transactions[] = [
                        'bank_account_id' => $bankAccount->id,
                        'value' => $transaction['value'] * 100,
                        'original_wording' => $transaction['original_wording'],
                        'simplified_wording' => $transaction['simplified_wording'],
                        'stemmed_wording' => $transaction['stemmed_wording'],
                        'wording' => $transaction['wording'],
                        'type' => $type,
                        'application_date' => $transaction['application_date'],
                    ];
                }
            }

            Transaction::insert($insert_transactions);
        } else {
            Notification::make('transactions_error')
                ->danger()
                ->title('Erreur')
                ->body('Une erreur est survenue lors de la récupération des transactions.')
                ->send();
        }
    }

    public function getAccountTransactions($auth_token, $account_id)
    {
        $request = Http::withToken($auth_token)->get("$this->api_url/users/me/accounts/$account_id/transactions");

        if ($request->successful()) {
            return $request->json()['transactions'];
        } else {
            Notification::make('transactions_error')
                ->danger()
                ->title('Erreur')
                ->body('Une erreur est survenue lors de la récupération des transactions.')
                ->send();
        }
    }
}
