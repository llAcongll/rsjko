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
        'bank_masuk',
        'bank_keluar',
        'tunai_masuk',
        'tunai_keluar',
        'saldo_fisik',
        'saldo_bank',
        'saldo_tunai',
        'selisih',
        'catatan_selisih',
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





