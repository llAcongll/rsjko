<?php

namespace App\Services;

use App\Models\DocumentSequence;
use Carbon\Carbon;

class NumberingService
{
    public function generateNoBukti($type, $date, $dalamSiklus, $siklus = 0)
    {
        $carbon = Carbon::parse($date);
        $year = $carbon->year;
        $romawi = $this->getRomanMonth($date);

        if ($type === 'LS') {
            // As requested: front is constant 0001, suffix increments (LS 1, LS 2, ...)
            $urut = 0; // LS doesn't use the global BUKTI sequence
            $urutStr = "0001";
            $noBukti = "{$urutStr}/BUKTI- {$type} {$dalamSiklus} /BLUD/39.01/RSJKO-EHD/{$romawi}/{$year}";
        } elseif ($type === 'GU') {
            // As requested: 0001/BUKTI -GU 1 /BLUD/39.01/RSJKO-EHD/II/2026
            // Using separate counter for each GU cycle
            $urut = DocumentSequence::nextNumber('BUKTI', $year, $siklus, $type);
            $urutStr = str_pad($urut, 4, '0', STR_PAD_LEFT);
            $noBukti = "{$urutStr}/BUKTI -{$type} {$siklus} /BLUD/39.01/RSJKO-EHD/{$romawi}/{$year}";
        } else {
            // For UP (or others) - Always use siklus 0 to ensure continuous numbering for the whole year
            $urut = DocumentSequence::nextNumber('BUKTI', $year, 0, $type);
            $urutStr = str_pad($urut, 4, '0', STR_PAD_LEFT);
            $noBukti = "{$urutStr}/BUKTI - {$type}/BLUD/39.01/RSJKO-EHD/{$romawi}/{$year}";
        }

        return [
            'no_bukti' => $noBukti,
            'no_bukti_urut' => $urut
        ];
    }

    public function generatePaketNumber($year, $type, $siklus, $dalamSiklus)
    {
        return DocumentSequence::nextNumber('PAKET_GLOBAL', $year);
    }

    public function generateSppNumber($year, $type, $siklus, $date = null, $dalamSiklus = null)
    {
        $num = DocumentSequence::nextNumber('SPP', $year);
        $numStr = str_pad($num, 4, '0', STR_PAD_LEFT);
        $romawi = $this->getRomanMonth($date);

        if ($type === 'UP') {
            $typeLabel = "UP-{$siklus}";
        } elseif ($type === 'GU') {
            $typeLabel = "GU-{$siklus}";
        } else {
            $typeLabel = "LS-{$dalamSiklus}";
        }

        return [
            'urut' => $num,
            'formatted' => "{$numStr}/SPP/{$typeLabel}/BLUD/RSJKO-EHD/{$romawi}/{$year}"
        ];
    }

    public function generateSpmNumber($year, $type, $siklus, $date = null, $dalamSiklus = null)
    {
        $num = DocumentSequence::nextNumber('SPM', $year);
        $numStr = str_pad($num, 4, '0', STR_PAD_LEFT);
        $romawi = $this->getRomanMonth($date);

        if ($type === 'UP') {
            $typeLabel = "UP-{$siklus}";
        } elseif ($type === 'GU') {
            $typeLabel = "GU-{$siklus}";
        } else {
            $typeLabel = "LS-{$dalamSiklus}";
        }

        return [
            'urut' => $num,
            'formatted' => "{$numStr}/SPM/{$typeLabel}/BLUD/RSJKO-EHD/{$romawi}/{$year}"
        ];
    }

    public function generateSp2dNumber($year)
    {
        $num = DocumentSequence::nextNumber('SP2D', $year);
        $numStr = str_pad($num, 4, '0', STR_PAD_LEFT);
        return [
            'urut' => $num,
            'formatted' => "{$numStr}/SP2D/BLUD/RSJKO-EHD/{$year}"
        ];
    }


    private function getRomanMonth($date = null)
    {
        $month = $date ? Carbon::parse($date)->month : now()->month;
        $romans = [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII'
        ];
        return $romans[$month] ?? 'I';
    }
}





