<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankAccountLedger extends Model
{
    protected $fillable = [
        'date',
        'type',
        'debit',
        'credit',
        'balance',
        'description',
        'ref_table',
        'ref_id'
    ];
}





