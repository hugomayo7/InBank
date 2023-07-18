<?php

namespace App\Interfaces;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;

interface PowensRepositoryInterface
{
    public function authenticate(Authenticatable|null $user, Request $request);

//    public function getAccounts($user);
//
//    public function getAccount($user, $account_id);
//
//    public function getTransactions($user, $account_id);
//
//    public function getTransaction($user, $account_id, $transaction_id);
//
//    public function getBalance($user, $account_id);
}
