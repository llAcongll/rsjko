<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArusKasMapping extends Model
{
    protected $fillable = [
        'kode_rekening_id',
        'tipe'
    ];

    public function kodeRekening()
    {
        return $this->belongsTo(KodeRekening::class, 'kode_rekening_id');
    }
}





