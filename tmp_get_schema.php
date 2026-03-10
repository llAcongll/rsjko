<?php
include 'vendor/autoload.php';
$app = include 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$tables = [
    'revenue_masters',
    'rekening_korans',
    'pendapatan_umum',
    'pendapatan_bpjs',
    'pendapatan_jaminan',
    'pendapatan_kerjasama',
    'pendapatan_lain',
    'penyesuaian_pendapatans'
];

foreach ($tables as $tbl) {
    echo "\n--- $tbl ---\n";
    try {
        $cols = DB::select("DESCRIBE $tbl");
        foreach ($cols as $col) {
            echo $col->Field . ' | ' . $col->Type . ' | ' . $col->Null . ' | ' . $col->Key . "\n";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}





