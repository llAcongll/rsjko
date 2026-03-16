<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = ['pendapatan_umum', 'pendapatan_bpjs', 'pendapatan_jaminan', 'pendapatan_kerjasama', 'pendapatan_lain', 'rekening_korans', 'bank_account_ledgers'];
foreach ($tables as $t) {
    $count = \DB::table($t)->where('total', 10661)->orWhere('jumlah', 10661)->orWhere('debit', 10661)->orWhere('credit', 10661)->count();
    if ($count > 0)
        echo "Found $count records in $t\n";
}

// Search for values that sum up to 10661?
$inc = \DB::table('pendapatan_umum')->whereYear('tanggal', 2026)->whereMonth('tanggal', 1)->get();
// maybe check if it's a specific record.
