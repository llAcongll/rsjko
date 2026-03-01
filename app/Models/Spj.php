<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Spj extends Model
{
    protected $table = 'spj';

    protected $fillable = [
        'spj_number',
        'spj_date',
        'bendahara_id',
        'siklus_up',
        'status',
    ];

    protected $casts = [
        'spj_date' => 'date:Y-m-d',
    ];

    public function bendahara()
    {
        return $this->belongsTo(User::class, 'bendahara_id');
    }

    public function items()
    {
        return $this->hasMany(SpjItem::class, 'spj_id');
    }

    public function disbursements()
    {
        return $this->hasMany(FundDisbursement::class, 'spj_id');
    }
}
