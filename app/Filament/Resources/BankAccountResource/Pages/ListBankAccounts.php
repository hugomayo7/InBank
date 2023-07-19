<?php

namespace App\Filament\Resources\BankAccountResource\Pages;

use App\Filament\Resources\BankAccountResource;
use App\Interfaces\PowensRepositoryInterface;
use Closure;
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
    }

    protected function getActions(): array
    {
        $powensDomainUrl = env('POWENS_DOMAIN_URL');
        $clientId = env('POWENS_CLIENT_ID');
        $redirectUri = env('POWENS_REDIRECT_URI') . 'bank-accounts';

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
