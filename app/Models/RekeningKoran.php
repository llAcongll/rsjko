<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RekeningKoran extends Model
{
    use HasFactory;

    protected $table = 'rekening_korans';

    protected $fillable = [
        'revenue_master_id',
        'tanggal',
        'tahun',
        'bank',
        'keterangan',
        'cd',
        'jumlah',
    ];

    protected $casts = [
        'tanggal' => 'date:Y-m-d',
        'tahun' => 'integer',
        'jumlah' => 'decimal:2',
    ];
}
