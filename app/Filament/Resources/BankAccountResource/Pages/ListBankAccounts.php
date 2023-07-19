<?php

namespace App\Filament\Resources\BankAccountResource\Pages;

use App\Filament\Resources\BankAccountResource;
use App\Repository\PowensRepository;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListBankAccounts extends ListRecords
{
    protected static string $resource = BankAccountResource::class;

    protected function getActions(): array
    {
        $powensDomainUrl = env('POWENS_DOMAIN_URL');
        $clientId = env('POWENS_CLIENT_ID');
        $redirectUri = env('POWENS_REDIRECT_URI');

        return [
            Action::make('add_account')
                ->url("$powensDomainUrl/auth/webview/connect?client_id=$clientId&redirect_uri=$redirectUri")
                ->label('Ajouter un compte')
                ->icon('heroicon-o-plus-circle'),
        ];
    }
}
