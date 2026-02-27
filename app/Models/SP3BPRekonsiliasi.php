<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SP3BPRekonsiliasi extends Model
{
    protected $table = 'sp3bp_rekonsiliasis';

    protected $fillable = [
        'sp3bp_id',
        'saldo_bank',
        'saldo_tunai',
        'saldo_buku',
        'selisih',
    ];

    public function sp3bp()
    {
        return $this->belongsTo(SP3BP::class, 'sp3bp_id');
    }
}
