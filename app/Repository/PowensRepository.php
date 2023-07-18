<?php

namespace App\Repository;

use App\Interfaces\PowensRepositoryInterface;
use App\Models\BankAccount;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class PowensRepository implements PowensRepositoryInterface
{
    private $api_url;

    public function __construct()
    {
        $this->api_url = env('POWENS_DOMAIN_URL');
    }

    public function authenticate(Authenticatable|null $user, Request $request)
    {
        $code = null;
        $connection_id = null;

        if (request()->has('code'))
            $code = request()->get('code');

        if (request()->has('connection_id'))
            $connection_id = request()->get('connection_id');

        if ($code && $connection_id) {

            if (!$user->bankAccounts()->where('connection_id', $connection_id)->exists()) {
                $auth_request = Http::post("$this->api_url/auth/token/access", [
                    'client_id' => env('POWENS_CLIENT_ID'),
                    'client_secret' => env('POWENS_CLIENT_SECRET'),
                    'code' => $code,
                    'connection_id' => $connection_id,
                ]);

                if ($auth_request->successful()) {

                    $auth_token = $auth_request->json()['access_token'];

                    $accounts_request = Http::withToken($auth_token)->get("$this->api_url/users/me/accounts?expand=connection[connector]");

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
                                'auth_token' => $auth_token,
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
                            ->body('Une erreur est survenue lors de la récupération de vos données bancaires.')
                            ->send();
                    }
                } else {
                    Notification::make('auth_error')
                        ->danger()
                        ->title('Authentification échouée')
                        ->body("Une erreur est survenue lors de l'authentification.")
                        ->send();
                }
            }
            //TODO: Check if user is already authenticated and send notification
            /*else {
                Notification::make('auth_error')
                    ->warning()
                    ->title('Authentification échouée')
                    ->body('Vous êtes déjà authentifié.')
                    ->send();
            }*/
        }
    }

//    public function getAccounts($user)
//    {
//        // TODO: Implement getAccounts() method.
//    }
//
//    public function getAccount($user, $account_id)
//    {
//        // TODO: Implement getAccount() method.
//    }
//
//    public function getTransactions($user, $account_id)
//    {
//        // TODO: Implement getTransactions() method.
//    }
//
//    public function getTransaction($user, $account_id, $transaction_id)
//    {
//        // TODO: Implement getTransaction() method.
//    }
//
//    public function getBalance($user, $account_id)
//    {
//        // TODO: Implement getBalance() method.
//    }
}
