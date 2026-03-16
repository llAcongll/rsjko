<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$mappedTunai = \DB::table('rekening_korans')
    ->join('revenue_masters', 'rekening_korans.revenue_master_id', '=', 'revenue_masters.id')
    ->where('revenue_masters.metode_pembayaran', 'TUNAI')
    ->whereYear('revenue_masters.tanggal', 2026)
    ->whereMonth('revenue_masters.tanggal', 1)
    ->sum('rekening_korans.jumlah');

echo "Mapped Tunai (Setoran): $mappedTunai\n";

$bkuOut = \DB::table('bku_penerimaan')
    ->whereYear('tanggal', 2026)
    ->whereMonth('tanggal', 1)
    ->sum('pengeluaran');
echo "BKU Tunai Out: $bkuOut\n";
