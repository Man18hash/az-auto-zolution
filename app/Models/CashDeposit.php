<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashDeposit extends Model
{
    protected $fillable = [
        'date',
        'description',
        'amount',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];
}
