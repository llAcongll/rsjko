<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LRKB extends Model
{
    protected $table = 'lrkbs';

    protected $fillable = [
        'tahun',
        'triwulan',
        'bulan',
        'tgl_rekonsiliasi',
        'saldo_awal',
        'pendapatan',
        'belanja',
        'pembiayaan',
        'saldo_akhir_buku',
        'saldo_fisik',
        'saldo_bank',
        'saldo_tunai',
        'selisih',
        'status',
        'created_by',
    ];

    public function details()
    {
        return $this->hasMany(LRKBDetail::class, 'lrkb_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
