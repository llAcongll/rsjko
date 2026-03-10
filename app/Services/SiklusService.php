<?php

namespace App\Services;

use App\Models\FundDisbursement;
use App\Models\DocumentSequence;
use Carbon\Carbon;

class SiklusService
{
    /**
     * Get the currently active UP cycle for the given year.
     */
    public function getActiveSiklus($year)
    {
        return FundDisbursement::where('tahun', $year)
            ->whereIn('type', ['UP', 'GU'])
            ->max('siklus_up') ?? 0;
    }

    /**
     * Start a new UP cycle.
     */
    public function startNewSiklus($year)
    {
        $last = $this->getActiveSiklus($year);
        return $last + 1;
    }

    /**
     * Get the next global package number.
     */
    public function getNextNomorPaket($year)
    {
        return DocumentSequence::nextNumber('PAKET_GLOBAL', $year);
    }

    /**
     * Get the next number within the specific cycle and type.
     */
    public function getNextNomorDalamSiklus($year, $siklus, $type)
    {
        // Use anti-collision counter based on (year, siklus_up, type)
        return DocumentSequence::nextNumber('CYCLE_INTERNAL', $year, $siklus, $type);
    }
}





