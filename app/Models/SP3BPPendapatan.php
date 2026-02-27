<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SP3BPPendapatan extends Model
{
    protected $table = 'sp3bp_pendapatans';

    protected $fillable = [
        'sp3bp_id',
        'kode_rekening',
        'uraian',
        'jumlah',
    ];

    public function sp3bp()
    {
        return $this->belongsTo(SP3BP::class, 'sp3bp_id');
    }
}
