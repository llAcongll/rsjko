<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KodeRekening extends Model
{
    protected $table = 'kode_rekening';

    protected $fillable = [
        'kode',
        'nama',
        'parent_id',
        'level',
        'tipe',
        'sumber_data',
        'is_active'
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->with('children');
    }

    public function anggaran()
    {
        return $this->hasMany(AnggaranRekening::class);
    }

    // ambil anggaran per tahun tertentu
    public function anggaranTahun($tahun)
    {
        return $this->hasOne(AnggaranRekening::class, 'kode_rekening_id')
            ->where('tahun', $tahun);
    }

}
