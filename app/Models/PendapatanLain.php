<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendapatanLain extends Model
{
    protected $table = 'pendapatan_lain';
    protected $fillable = [
        'tanggal',
        'tahun',
        'nama_pasien',
        'ruangan_id',
        'mou_id',
        'transaksi',
        'metode_pembayaran',
        'bank',
        'metode_detail',
        'rs_tindakan',
        'rs_obat',
        'pelayanan_tindakan',
        'pelayanan_obat',
        'total'
    ];
    protected $casts = [
        'tanggal' => 'date',
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

    public function mou()
    {
        return $this->belongsTo(Mou::class);
    }
}
