<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenyesuaianPendapatan extends Model
{
    protected $fillable = [
        'tanggal',
        'tahun',
        'kategori',
        'sub_kategori',
        'perusahaan_id',
        'potongan',
        'administrasi_bank',
        'keterangan'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'tahun' => 'integer',
        'potongan' => 'decimal:2',
        'administrasi_bank' => 'decimal:2',
    ];

    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class);
    }
}
