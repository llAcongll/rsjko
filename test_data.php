<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;

$start = '2026-01-01';
$end = '2026-01-31';
$table = 'pendapatan_umum';

$sum = DB::table($table)->whereExists(function ($query) use ($table) {
    $query->select(DB::raw(1))
        ->from('revenue_masters')
        ->whereColumn('revenue_masters.id', "{$table}.revenue_master_id");
})->whereBetween('tanggal', [$start, $end])->sum('total');

echo "Sum for $table: " . number_format($sum, 2) . "\n";

$sptjb_data = app(\App\Services\ReportService::class)->getAnggaranData('PENDAPATAN', $start, $end, 2026, 3);
echo "LRA Data Count: " . count($sptjb_data['data']) . "\n";

foreach($sptjb_data['data'] as $item) {
    if($item['realisasi_total'] > 0) {
        echo "Row: {$item['nama']} Code: {$item['kode']} Real: " . number_format($item['realisasi_total'], 2) . "\n";
    }
}
