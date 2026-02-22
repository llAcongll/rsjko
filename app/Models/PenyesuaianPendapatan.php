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
        'tahun_piutang',
        'pelunasan',
        'potongan',
        'administrasi_bank',
        'keterangan'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'tahun' => 'integer',
        'pelunasan' => 'decimal:2',
        'potongan' => 'decimal:2',
        'administrasi_bank' => 'decimal:2',
    ];

    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class);
    }
}
