<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnggaranRincian extends Model
{
    use HasFactory;

    protected $table = 'anggaran_rincian';

    protected $fillable = [
        'anggaran_rekening_id',
        'uraian',
        'volume',
        'satuan',
        'tarif',
        'subtotal',
    ];

    protected $casts = [
        'volume' => 'float',
        'tarif' => 'float',
        'subtotal' => 'float',
    ];

    public function anggaran()
    {
        return $this->belongsTo(AnggaranRekening::class, 'anggaran_rekening_id');
    }
}
