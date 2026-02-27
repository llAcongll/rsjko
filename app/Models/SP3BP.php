<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SP3BP extends Model
{
    protected $table = 'sp3bps';

    protected $fillable = [
        'periode_id',
        'saldo_awal',
        'pendapatan',
        'belanja',
        'pembiayaan_terima',
        'pembiayaan_keluar',
        'saldo_akhir',
        'selisih',
        'status',
    ];

    public function periode()
    {
        return $this->belongsTo(PengesahanPeriode::class, 'periode_id');
    }

    public function detailPendapatan()
    {
        return $this->hasMany(SP3BPPendapatan::class, 'sp3bp_id');
    }

    public function detailBelanja()
    {
        return $this->hasMany(SP3BPBelanja::class, 'sp3bp_id');
    }

    public function rekonsiliasi()
    {
        return $this->hasOne(SP3BPRekonsiliasi::class, 'sp3bp_id');
    }
}
