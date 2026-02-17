<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnggaranRekening extends Model
{
    protected $table = 'anggaran_rekening';

    protected $fillable = [
        'kode_rekening_id',
        'tahun',
        'nilai',
    ];

    /* =========================
       RELATION
    ========================= */

    // Anggaran → Kode Rekening
    public function kodeRekening()
    {
        return $this->belongsTo(KodeRekening::class, 'kode_rekening_id');
    }

    // Anggaran Rekening → Rincian
    public function rincian()
    {
        return $this->hasMany(AnggaranRincian::class, 'anggaran_rekening_id');
    }
}
