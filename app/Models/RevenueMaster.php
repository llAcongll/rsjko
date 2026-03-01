<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RevenueMaster extends Model
{
    protected $table = 'revenue_masters';

    protected $fillable = [
        'tanggal',
        'tanggal_rk',
        'tahun',
        'kategori',
        'no_bukti',
        'keterangan',
        'total_rs',
        'total_pelayanan',
        'total_all',
        'status',
        'is_posted',
    ];

    protected $casts = [
        'tanggal' => 'date:Y-m-d',
        'tanggal_rk' => 'date:Y-m-d',
        'tahun' => 'integer',
        'total_rs' => 'decimal:2',
        'total_pelayanan' => 'decimal:2',
        'total_all' => 'decimal:2',
        'is_posted' => 'boolean',
    ];

    public function pendapatanUmums()
    {
        return $this->hasMany(PendapatanUmum::class, 'revenue_master_id');
    }

    public function pendapatanBpjs()
    {
        return $this->hasMany(PendapatanBpjs::class, 'revenue_master_id');
    }

    public function pendapatanJaminans()
    {
        return $this->hasMany(PendapatanJaminan::class, 'revenue_master_id');
    }

    public function pendapatanLains()
    {
        return $this->hasMany(PendapatanLain::class, 'revenue_master_id');
    }

    public function pendapatanKerjasamas()
    {
        return $this->hasMany(PendapatanKerjasama::class, 'revenue_master_id');
    }
}
