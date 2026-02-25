<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $date
 * @property string $type
 * @property string $ref_table
 * @property int $ref_id
 * @property string|null $description
 * @property string $debit
 * @property string $credit
 * @property string $balance
 */
class TreasurerCash extends Model
{
    protected $table = 'treasurer_cash';

    protected $fillable = [
        'date',
        'type',
        'ref_table',
        'ref_id',
        'debit',
        'credit',
        'balance',
    ];

    protected $casts = [
        'date' => 'date',
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
        'balance' => 'decimal:2',
    ];
}
