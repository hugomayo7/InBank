<?php

namespace App\Console\Commands;

use App\Interfaces\PowensRepositoryInterface;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;

class UpdateBankAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-bank-accounts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mettre à jour les comptes bancaires';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $connections_ids = [];

        foreach (User::first()->bankAccounts as $bankAccount) {
            $connections_ids[] = $bankAccount->connection_id;
        }

        App::get(PowensRepositoryInterface::class)->updateConnections(User::first()->auth_token, $connections_ids);
    }
}
