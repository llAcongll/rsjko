<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengeluaran extends Model
{
    use HasFactory;

    protected $table = 'pengeluaran';

    protected $fillable = [
        'tanggal',
        'kategori',
        'kode_rekening_id',
        'uraian',
        'nominal',
        'metode_pembayaran',
        'no_spm',
        'no_sp2d',
        'no_spp',
        'no_spp_index',
        'no_spp_metode_index',
        'no_spm_index',
        'no_spm_metode_index',
        'no_sp2d_index',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'nominal' => 'decimal:2',
    ];

    public function kodeRekening()
    {
        return $this->belongsTo(KodeRekening::class, 'kode_rekening_id');
    }
}
