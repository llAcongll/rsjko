<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FundDisbursement extends Model
{
    protected $fillable = [
        'tahun',
        'nomor_paket',
        'siklus_up',
        'type',
        'nomor_dalam_siklus',
        'sp2d_date',
        'sp2d_no',
        'spm_no',
        'spp_no',
        'value',
        'recipient_party',
        'destination_account',
        'spj_id',
        'expenditure_id',
        'kode_rekening_id',
        'uraian',
        'status',
        'description',
        'number_locked_at',
        'no_bukti',
        'no_bukti_urut',
        'spp_urut',
        'spm_urut',
        'sp2d_urut',
    ];

    protected $appends = [
        'paket_number',
        'siklus_number',
    ];

    protected $casts = [
        'sp2d_date' => 'date:Y-m-d',
        'value' => 'decimal:2',
    ];

    public function getPaketNumberAttribute()
    {
        return str_pad($this->nomor_paket, 4, '0', STR_PAD_LEFT);
    }

    public function getSiklusNumberAttribute()
    {
        if ($this->type !== 'GU' || !$this->siklus_up) {
            return '-';
        }
        return "{$this->type}-{$this->siklus_up} (Siklus {$this->nomor_dalam_siklus})";
    }

    public function spj()
    {
        return $this->belongsTo(Spj::class);
    }

    public function expenditure()
    {
        return $this->belongsTo(Expenditure::class);
    }

    public function kodeRekening()
    {
        return $this->belongsTo(KodeRekening::class, 'kode_rekening_id');
    }

    public function expenditures()
    {
        return $this->hasMany(Expenditure::class, 'fund_disbursement_id');
    }
}
