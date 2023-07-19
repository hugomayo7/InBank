<?php

namespace App\Models;

use App\Casts\Price;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'user_id',
        'connector_uuid',
        'bank_name',
        'iban',
        'last_updated_at',
        'account_id',
        'connection_id',
        'original_name'
    ];

    protected $casts = [
        'last_updated_at' => 'datetime',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
