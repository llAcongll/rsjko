<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expenditure extends Model
{
    protected $fillable = [
        'fund_disbursement_id',
        'spending_date',
        'kode_rekening_id',
        'description',
        'gross_value',
        'tax',
        'net_value',
        'spending_type',
        'siklus_up',
        'vendor',
        'proof_number',
        'no_bukti',
        'no_bukti_urut',
        'nomor_dalam_siklus',
        'number_locked_at',
        'created_by',
    ];

    protected $casts = [
        'spending_date' => 'date:Y-m-d',
        'gross_value' => 'decimal:2',
        'tax' => 'decimal:2',
        'net_value' => 'decimal:2',
    ];

    public function kodeRekening()
    {
        return $this->belongsTo(KodeRekening::class, 'kode_rekening_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function spjItems()
    {
        return $this->hasMany(SpjItem::class);
    }

    public function getIsInSpjAttribute()
    {
        return $this->spjItems()->exists();
    }

    public function fundDisbursement()
    {
        return $this->belongsTo(FundDisbursement::class);
    }
}
