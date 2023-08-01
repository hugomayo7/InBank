<?php

namespace App\Interfaces;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;

interface PowensRepositoryInterface
{
    public function authenticate(Authenticatable|null $user, Request $request);

    public function getAccounts(string $auth_token);

    public function getAccount(string $auth_token, string $account_id);

    public function getConnectionAccounts(string $auth_token, $connection_id);

    public function getTotalBalance(string $auth_token);

    public function deleteConnection(string $auth_token, string $connection_id);

    public function deleteConnections(string $auth_token, array $connection_ids);

    public function updateConnection(string $auth_token, string $connection_id);

    public function updateConnections(string $auth_token, array $connection_ids);

    public function refreshData();

    public function fetchAllTransactions(string $auth_token);

    public function getAccountTransactions(string $auth_token, string $account_id);
}
