<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BkuPenerimaan extends Model
{
    use HasFactory;

    protected $table = 'bku_penerimaan';

    protected $fillable = [
        'tanggal',
        'uraian',
        'penerimaan',
        'pengeluaran',
        'saldo',
        'sumber',
        'reference_id'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'penerimaan' => 'decimal:2',
        'pengeluaran' => 'decimal:2',
        'saldo' => 'decimal:2'
    ];
}





