<?php

namespace App\Models;

use App\Casts\Price;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    const CARD_TYPE = 'Carte';
    const TRANSFER_TYPE = 'Virement';
    const ORDER_TYPE = 'Prélèvement';
    const WITHDRAWAL_TYPE = 'Retrait';
    const BANK_TYPE = 'Frais bancaires';
    const PAYBACK_TYPE = 'Remboursement';
    const UNKNOWN_TYPE = 'Inconnu';

    protected $fillable = [
        'transaction_id',
        'bank_account_id',
        'value',
        'original_wording',
        'simplified_wording',
        'stemmed_wording',
        'wording',
        'type',
        'application_date',
    ];

    protected $casts = [
        'value' => Price::class,
    ];

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }
}
