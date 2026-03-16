<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$start = '2026-01-01';
$end = '2026-01-31';
$year = 2026;

echo "--- BKU PENERIMAAN ---\n";
echo "Penerimaan: " . \DB::table('bku_penerimaan')->whereBetween('tanggal', [$start, $end])->sum('penerimaan') . "\n";
echo "Pengeluaran (Setor): " . \DB::table('bku_penerimaan')->whereBetween('tanggal', [$start, $end])->sum('pengeluaran') . "\n";

echo "\n--- BANK ACCOUNT LEDGER (PENGELUARAN) ---\n";
echo "Debit: " . \DB::table('bank_account_ledgers')->whereBetween('date', [$start, $end])->sum('debit') . "\n";
echo "Credit: " . \DB::table('bank_account_ledgers')->whereBetween('date', [$start, $end])->sum('credit') . "\n";

echo "\n--- REKENING KORAN (PENDAPATAN) ---\n";
echo "Credit (Input): " . \DB::table('rekening_korans')->where('tahun', $year)->whereBetween('tanggal', [$start, $end])->where('cd', 'C')->where('is_saldo_awal', false)->sum('jumlah') . "\n";

echo "\n--- TREASURER CASH (PENGELUARAN) ---\n";
echo "Debit: " . \DB::table('treasurer_cash')->whereBetween('date', [$start, $end])->sum('debit') . "\n";
echo "Credit: " . \DB::table('treasurer_cash')->whereBetween('date', [$start, $end])->sum('credit') . "\n";

echo "\n--- EXPENDITURES ---\n";
echo "Gross: " . \DB::table('expenditures')->whereBetween('spending_date', [$start, $end])->sum('gross_value') . "\n";
