<?php

namespace App\Services;

use App\Models\KodeRekening;
use Illuminate\Support\Facades\DB;

class BusinessBudgetService
{
    /**
     * Get RBA Data (Rencana Bisnis Anggaran)
     * 
     * @param int $tahun
     * @return array
     */
    public function getRbaData($tahun)
    {
        // 1. TOTAL PENDAPATAN
        $totalPendapatan = DB::table('anggaran_rekening as ar')
            ->join('kode_rekening as kr', 'ar.kode_rekening_id', '=', 'kr.id')
            ->where('ar.tahun', $tahun)
            ->where('kr.category', 'PENDAPATAN')
            ->sum('ar.nilai');

        // 2. TOTAL BELANJA
        $totalBelanja = DB::table('anggaran_rekening as ar')
            ->join('kode_rekening as kr', 'ar.kode_rekening_id', '=', 'kr.id')
            ->where('ar.tahun', $tahun)
            ->where('kr.category', 'PENGELUARAN')
            ->sum('ar.nilai');

        // 3. SURPLUS/DEFISIT
        $surplusDefisit = $totalPendapatan - $totalBelanja;

        // Breakdown for the table
        $breakdown = DB::table('kode_rekening as kr')
            ->select(
                'kr.id',
                'kr.kode',
                'kr.nama',
                'kr.category',
                'kr.level',
                DB::raw("(
                    SELECT IFNULL(SUM(ar.nilai), 0) 
                    FROM anggaran_rekening ar 
                    JOIN kode_rekening kr2 ON ar.kode_rekening_id = kr2.id 
                    WHERE ar.tahun = $tahun AND kr2.kode LIKE CONCAT(kr.kode, '%')
                ) as anggaran")
            )
            ->where('kr.is_active', true)
            ->where('kr.level', '<=', 3) // Typically high-level summary for RBA
            ->orderBy('kr.kode')
            ->get();

        return [
            'tahun' => $tahun,
            'summary' => [
                'pendapatan' => (float) $totalPendapatan,
                'belanja' => (float) $totalBelanja,
                'surplus_defisit' => (float) $surplusDefisit
            ],
            'breakdown' => $breakdown
        ];
    }
}





