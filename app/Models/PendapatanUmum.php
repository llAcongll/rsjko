<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendapatanUmum extends Model
{
    protected $table = 'pendapatan_umum';

    protected $fillable = [
        'revenue_master_id',
        'tanggal',
        'tahun',
        'nama_pasien',
        'ruangan_id',
        'metode_pembayaran',
        'bank',
        'metode_detail',
        'rs_tindakan',
        'rs_obat',
        'pelayanan_tindakan',
        'pelayanan_obat',
        'total',
    ];

    protected $casts = [
        'tanggal' => 'date:Y-m-d',
        'tahun' => 'integer',
        'rs_tindakan' => 'decimal:2',
        'rs_obat' => 'decimal:2',
        'pelayanan_tindakan' => 'decimal:2',
        'pelayanan_obat' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function ruangan()
    {
        return $this->belongsTo(Ruangan::class);
    }

    public function revenueMaster()
    {
        return $this->belongsTo(RevenueMaster::class, 'revenue_master_id');
    }
}
