<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoMapping extends Model
{
    use HasFactory;

    protected $table = 'lo_mappings';

    protected $fillable = [
        'kode_rekening_id',
        'kategori',
    ];

    public function kodeRekening()
    {
        return $this->belongsTo(KodeRekening::class, 'kode_rekening_id');
    }
}





