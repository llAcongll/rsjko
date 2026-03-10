<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pengeluaran; // Old model
use App\Models\Expenditure; // New model
use App\Models\FundDisbursement;
use Illuminate\Support\Facades\DB;

class MigrateExpenditureData extends Command
{
    protected $signature = 'migrate:expenditure-data';
    protected $description = 'Migrate data from old pengeluaran table to new Audit-Safe structure';

    public function handle()
    {
        $oldData = Pengeluaran::all();
        $this->info("Found {$oldData->count()} records to migrate.");

        DB::transaction(function () use ($oldData) {
            foreach ($oldData as $old) {
                // 1. Create Expenditure (Economic Event)
                $exp = Expenditure::create([
                    'spending_date' => $old->tanggal,
                    'kode_rekening_id' => $old->kode_rekening_id,
                    'description' => $old->uraian,
                    'gross_value' => $old->nominal,
                    'tax' => $old->potongan_pajak ?? 0,
                    'net_value' => $old->total_dibayarkan ?? ($old->nominal - ($old->potongan_pajak ?? 0)),
                    'spending_type' => $old->metode_pembayaran === 'LS' ? 'LS' : 'UP',
                    'vendor' => null,
                    'proof_number' => null,
                    'created_by' => 1, // Assume admin for legacy data
                ]);

                // 2. If it has SP2D, create Fund Disbursement (Cash Event)
                if ($old->no_sp2d || $old->no_spm || $old->no_spp) {
                    FundDisbursement::create([
                        'type' => $old->metode_pembayaran ?: 'UP',
                        'sp2d_date' => $old->tanggal, // Approximation
                        'sp2d_no' => $old->no_sp2d,
                        'spm_no' => $old->no_spm,
                        'spp_no' => $old->no_spp,
                        'value' => $old->nominal,
                        'expenditure_id' => $old->metode_pembayaran === 'LS' ? $exp->id : null,
                    ]);
                }

                // Note: Rebuilding ledger will be done after migration
            }
        });

        $this->info("Migration completed successfully.");
    }
}





