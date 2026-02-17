<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Piutang extends Model
{
    protected $fillable = [
        'tanggal',
        'tahun',
        'perusahaan_id',
        'bulan_pelayanan',
        'jumlah_piutang',
        'potongan',
        'administrasi_bank',
        'total_diterima',
        'status',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'tahun' => 'integer',
        'jumlah_piutang' => 'decimal:2',
        'potongan' => 'decimal:2',
        'administrasi_bank' => 'decimal:2',
        'total_diterima' => 'decimal:2',
    ];

    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class, 'perusahaan_id');
    }
}
