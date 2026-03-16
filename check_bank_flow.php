<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$start = '2026-01-01';
$end = '2026-01-31';
$year = 2026;

$saBankAwal = 191563493.48; // From my previous logic check

// PENGELUARAN ACCOUNT (ledgers)
$bankInExp = \DB::table('bank_account_ledgers')->whereBetween('date', [$start, $end])->where('type', '!=', 'SALDO_AWAL')->sum('debit');
$bankOutExp = \DB::table('bank_account_ledgers')->whereBetween('date', [$start, $end])->sum('credit');

// PENDAPATAN ACCOUNT (rk)
$bankInInc = \DB::table('rekening_korans')->where('tahun', $year)->whereBetween('tanggal', [$start, $end])->where('is_saldo_awal', false)->where('cd', 'C')->sum('jumlah');
$bankOutInc = \DB::table('rekening_korans')->where('tahun', $year)->whereBetween('tanggal', [$start, $end])->where('is_saldo_awal', false)->where('cd', 'D')->sum('jumlah');

echo "saBankAwal: $saBankAwal\n";
echo "bankInExp (C): $bankInExp\n";
echo "bankInInc (C): $bankInInc\n";
echo "bankOutExp (D): $bankOutExp\n";
echo "bankOutInc (D): $bankOutInc\n";

$bankIn = $saBankAwal + $bankInExp + $bankInInc;
$bankOut = $bankOutExp + $bankOutInc;

echo "bankIn: $bankIn\n";
echo "bankOut: $bankOut\n";
echo "Net Flow (In - Out): " . ($bankIn - $bankOut) . "\n";

// Final Balance from Services
$r = app(\App\Services\ReportService::class);
$i = app(\App\Services\IncomeCashBookService::class);
$bkuExp = $r->getBkuData($year, 1);
$bkuInc = $i->getLedgerData($year, 1);
$fb = ($bkuExp['summary']['final_bank'] ?? 0) + ($bkuInc['summary']['bank_brk'] ?? 0) + ($bkuInc['summary']['bank_bsi'] ?? 0);
echo "Final Balance (Service): $fb\n";
echo "Diff (NetFlow - FB): " . (($bankIn - $bankOut) - $fb) . "\n";
