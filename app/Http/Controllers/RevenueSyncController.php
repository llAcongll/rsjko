<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RevenueMaster;
use Illuminate\Support\Facades\DB;
use App\Models\ActivityLog;

class RevenueSyncController extends Controller
{
    public function syncOrphans(Request $request)
    {
        // Permission check: only admin or specifically permitted users
        abort_unless(auth()->user()->isAdmin() || auth()->user()->hasPermission('REVENUE_SYNC'), 403);

        $tables = [
            'UMUM' => 'pendapatan_umum',
            'BPJS' => 'pendapatan_bpjs',
            'JAMINAN' => 'pendapatan_jaminan',
            'LAIN' => 'pendapatan_lain',
            'KERJASAMA' => 'pendapatan_kerjasama'
        ];

        $results = [];
        $totalProcessed = 0;

        DB::beginTransaction();
        try {
            foreach ($tables as $kategori => $table) {
                if (!\Illuminate\Support\Facades\Schema::hasTable($table))
                    continue;

                $orphans = DB::table($table)
                    ->whereNull('revenue_master_id')
                    ->select('tanggal', 'tahun', DB::raw('count(*) as count'))
                    ->groupBy('tanggal', 'tahun')
                    ->get();

                $processedInCategory = 0;

                foreach ($orphans as $group) {
                    // Find or create master
                    $master = RevenueMaster::firstOrCreate([
                        'tanggal' => $group->tanggal,
                        'tahun' => $group->tahun,
                        'kategori' => $kategori
                    ], [
                        'keterangan' => "Sinkronisasi Otomatis $kategori - " . ($group->tanggal),
                        'total_rs' => 0,
                        'total_pelayanan' => 0,
                        'total_all' => 0,
                        'is_posted' => false
                    ]);

                    // Link orphans to this master
                    $affected = DB::table($table)
                        ->whereNull('revenue_master_id')
                        ->where('tanggal', $group->tanggal)
                        ->where('tahun', $group->tahun)
                        ->update(['revenue_master_id' => $master->id]);

                    $processedInCategory += $affected;

                    // Recalculate master totals
                    \App\Http\Controllers\RevenueMasterController::recalculate($master->id);
                }

                $results[$kategori] = $processedInCategory;
                $totalProcessed += $processedInCategory;
            }

            if ($totalProcessed > 0) {
                ActivityLog::log(
                    'SYNC',
                    'REVENUE_MASTER',
                    "Sinkronisasi otomatis data rincian pendapatan: $totalProcessed record diproses",
                    null,
                    null,
                    $results
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil menyinkronkan $totalProcessed data pendapatan.",
                'details' => $results
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal sinkronisasi: ' . $e->getMessage()
            ], 500);
        }
    }
}
