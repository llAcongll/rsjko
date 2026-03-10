<?php

namespace App\Services;

use App\Models\KodeRekening;
use Illuminate\Support\Facades\DB;

class BudgetPlanService
{
    /**
     * Get RKA Data (Rencana Kerja Anggaran)
     * 
     * @param int $tahun
     * @return array
     */
    public function getRkaData($tahun)
    {
        $data = DB::table('kode_rekening as kr')
            ->select(
                'kr.id',
                'kr.kode',
                'kr.nama',
                'kr.level',
                'kr.category',
                'kr.parent_id',
                DB::raw("(
                    SELECT IFNULL(SUM(ar.nilai), 0) 
                    FROM anggaran_rekening ar 
                    JOIN kode_rekening kr2 ON ar.kode_rekening_id = kr2.id 
                    WHERE ar.tahun = $tahun AND kr2.kode LIKE CONCAT(kr.kode, '%')
                ) as anggaran")
            )
            ->where('kr.is_active', true)
            ->orderBy('kr.kode')
            ->get();

        return [
            'tahun' => $tahun,
            'data' => $data
        ];
    }
}





