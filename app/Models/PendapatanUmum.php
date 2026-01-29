<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendapatanUmum extends Model
{
    protected $table = 'pendapatan_umum';

    protected $fillable = [
        'tanggal',
        'nama_pasien',
        'ruangan_id',
        'metode_pembayaran',
        'bank_id',
        'metode_detail',
        'rs_tindakan',
        'rs_obat',
        'pelayanan_tindakan',
        'pelayanan_obat',
        'total',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'rs_tindakan' => 'integer',
        'rs_obat' => 'integer',
        'pelayanan_tindakan' => 'integer',
        'pelayanan_obat' => 'integer',
        'total' => 'integer',
    ];

    public function ruangan()
    {
        return $this->belongsTo(Ruangan::class);
    }
}
