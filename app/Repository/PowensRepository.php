<?php

namespace App\Repository;

use App\Interfaces\PowensRepositoryInterface;
use App\Models\BankAccount;
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
                        'connection_id' => $account['id_connection'],
                        'bank_name' => $account['connection']['connector']['name'],
                        'iban' => $account['iban'],
                        'account_id' => $account['id'],
                        'last_updated_at' => $account['last_update'],
                        'original_name' => $account['original_name'],
                    ]);

                    if ($bankAccount->wasRecentlyCreated) {
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

        return round($total, 2) . ' €' ?? '0';
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
        $request = Http::withToken($auth_token)->get("$this->api_url/users/me/connections/$connection_id/accounts");

        if ($request->successful()) {
            $bankAccounts = BankAccount::where('connection_id', $connection_id)->get();
            $fetchBankAccounts = $request->json()['accounts'];

            $bankAccounts->map(function ($bankAccount) use ($fetchBankAccounts) {
                $fetchBankAccount = collect($fetchBankAccounts)->firstWhere('id', $bankAccount->account_id);

                if ($fetchBankAccount && Carbon::parse($fetchBankAccount['last_update'])->isPast()) {
                    $bankAccount->update([
                        'last_updated_at' => $fetchBankAccount['last_update'],
                        'balance' => $fetchBankAccount['balance'],
                    ]);
                }
            });

            Notification::make('connection_updated')
                ->success()
                ->title('Connexion mise à jour')
                ->body('La connexion a été mise à jour avec succès.')
                ->send();
        } else {
            Notification::make('connection_updated')
                ->danger()
                ->title('Erreur')
                ->body('Une erreur est survenue lors de la mise à jour de la connexion.')
                ->send();
        }
    }

    public function updateConnections($auth_token, $connection_ids)
    {
        $ids = array_unique($connection_ids);
        foreach ($ids as $connection_id) {
            $this->updateConnection($auth_token, $connection_id);
        }
    }
}
