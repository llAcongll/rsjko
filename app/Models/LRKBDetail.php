<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LRKBDetail extends Model
{
    protected $table = 'lrkb_details';

    protected $fillable = [
        'lrkb_id',
        'jenis',
        'uraian',
        'jumlah',
    ];

    public function lrkb()
    {
        return $this->belongsTo(LRKB::class, 'lrkb_id');
    }
}
