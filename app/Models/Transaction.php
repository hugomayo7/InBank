<?php

namespace App\Models;

use App\Casts\Price;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
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
