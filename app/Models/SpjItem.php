<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpjItem extends Model
{
    protected $fillable = [
        'spj_id',
        'expenditure_id',
    ];

    public function spj()
    {
        return $this->belongsTo(Spj::class);
    }

    public function expenditure()
    {
        return $this->belongsTo(Expenditure::class);
    }
}
