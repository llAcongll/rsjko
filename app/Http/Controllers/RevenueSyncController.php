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

                // 1. Identify data that is either unlinked OR linked to a DRAFT master
                $candidates = DB::table($table)
                    ->leftJoin('revenue_masters', "$table.revenue_master_id", '=', 'revenue_masters.id')
                    ->where(function ($q) use ($table) {
                        $q->whereNull("$table.revenue_master_id")
                            ->orWhere('revenue_masters.is_posted', false);
                    })
                    ->select("$table.tanggal", "$table.tahun", "$table.metode_pembayaran", DB::raw('count(*) as count'))
                    ->groupBy("$table.tanggal", "$table.tahun", "$table.metode_pembayaran")
                    ->get();

                // 2. Unlink all data currently in DRAFT status to reset them safely
                DB::table($table)
                    ->whereIn('revenue_master_id', function ($q) {
                        $q->select('id')->from('revenue_masters')->where('is_posted', false);
                    })
                    ->update(['revenue_master_id' => null]);

                $processedInCategory = 0;

                foreach ($candidates as $group) {
                    $mType = ($group->metode_pembayaran === 'TUNAI') ? 'TUNAI' : 'NON-TUNAI';

                    // Find or create master including payment method
                    $master = RevenueMaster::firstOrCreate([
                        'tanggal' => $group->tanggal,
                        'tahun' => $group->tahun,
                        'kategori' => $kategori,
                        'metode_pembayaran' => $mType
                    ], [
                        'keterangan' => "Sinkronisasi Otomatis $kategori [$mType] - " . ($group->tanggal),
                        'total_rs' => 0,
                        'total_pelayanan' => 0,
                        'total_all' => 0,
                        'is_posted' => false
                    ]);

                    // Link data to this master with matching method
                    $affected = DB::table($table)
                        ->whereNull('revenue_master_id')
                        ->where('tanggal', $group->tanggal)
                        ->where('tahun', $group->tahun)
                        ->where(function ($q) use ($group) {
                            if ($group->metode_pembayaran === 'TUNAI') {
                                $q->where('metode_pembayaran', 'TUNAI');
                            } else {
                                $q->where('metode_pembayaran', '!=', 'TUNAI');
                            }
                        })
                        ->update(['revenue_master_id' => $master->id]);

                    $processedInCategory += $affected;

                    // Recalculate master totals
                    \App\Http\Controllers\RevenueMasterController::recalculate($master->id);
                }

                $results[$kategori] = $processedInCategory;
                $totalProcessed += $processedInCategory;

                // 3. Cleanup empty DRAFT masters for this category
                RevenueMaster::where('kategori', $kategori)
                    ->where('is_posted', false)
                    ->where('total_all', 0)
                    ->delete();
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
