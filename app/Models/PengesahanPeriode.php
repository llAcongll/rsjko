<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengesahanPeriode extends Model
{
    protected $fillable = [
        'bulan',
        'triwulan',
        'tahun',
        'status',
        'tgl_pengesahan',
        'created_by',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sp3bp()
    {
        return $this->hasOne(SP3BP::class, 'periode_id');
    }
}





