<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DocumentSequence extends Model
{
    protected $fillable = ['type', 'tahun', 'siklus_up', 'sub_key', 'last_number'];

    /**
     * Get the next number for a given context using transactional locking.
     */
    public static function nextNumber($type, $year, $siklus = 0, $subKey = '')
    {
        return DB::transaction(function () use ($type, $year, $siklus, $subKey) {
            $seq = self::where('type', $type)
                ->where('tahun', $year)
                ->where('siklus_up', $siklus)
                ->where('sub_key', $subKey)
                ->lockForUpdate()
                ->first();

            if (!$seq) {
                $seq = self::create([
                    'type' => $type,
                    'tahun' => $year,
                    'siklus_up' => $siklus,
                    'sub_key' => $subKey,
                    'last_number' => 0
                ]);
            }

            // Sync with actual database state (Self-Healing)
            $actualMax = self::getActualMax($type, $year, $siklus, $subKey);
            $seq->last_number = ($actualMax > 0) ? $actualMax : 0;

            $seq->last_number++;
            $seq->save();

            return $seq->last_number;
        });
    }

    private static function getActualMax($type, $year, $siklus, $subKey)
    {
        if ($type === 'BUKTI') {
            $query = \App\Models\Expenditure::whereYear('spending_date', $year)
                ->where('spending_type', $subKey);

            if (in_array($subKey, ['UP', 'GU'])) {
                $query->where('siklus_up', $siklus);
            }

            return $query->max('no_bukti_urut') ?? 0;
        }

        if ($type === 'PAKET_GLOBAL') {
            return \App\Models\FundDisbursement::where('tahun', $year)->max('nomor_paket') ?? 0;
        }

        if ($type === 'CYCLE_INTERNAL') {
            $query = \App\Models\FundDisbursement::where('tahun', $year)
                ->where('type', $subKey);

            // LS follows a global sequence for the year.
            // But we only count those that have an SPP number (not "Saldo Dana")
            if ($subKey === 'LS') {
                $query->whereNotNull('spp_no');
            }

            // UP and GU stay cycle-specific.
            if ($subKey === 'UP' || $subKey === 'GU') {
                $query->where('siklus_up', $siklus);
            }

            return $query->max('nomor_dalam_siklus') ?? 0;
        }

        if ($type === 'BUKTI_CYCLE') {
            $query = \App\Models\Expenditure::whereYear('spending_date', $year)
                ->where('spending_type', $subKey);

            if (in_array($subKey, ['UP', 'GU'])) {
                $query->where('siklus_up', $siklus);
            }

            return $query->max('nomor_dalam_siklus') ?? 0;
        }

        if ($type === 'SPP') {
            return \App\Models\FundDisbursement::where('tahun', $year)->max('spp_urut') ?? 0;
        }

        if ($type === 'SPM') {
            return \App\Models\FundDisbursement::where('tahun', $year)->max('spm_urut') ?? 0;
        }

        if ($type === 'SP2D') {
            return \App\Models\FundDisbursement::where('tahun', $year)->max('sp2d_urut') ?? 0;
        }

        return 0;
    }
}
