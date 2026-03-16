<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\DB;

$start = '2026-01-01';
$end = '2026-01-31';

function calculateRealisasiRevenue($sumberData, $tahun, $startDate, $endDate) {
    switch ($sumberData) {
        case 'PASIEN_UMUM':
            return DB::table('pendapatan_umum as t')
                ->join('revenue_masters as rm', 't.revenue_master_id', '=', 'rm.id')
                ->where('rm.tahun', $tahun)
                ->whereIn('rm.is_posted', [0, 1])
                ->whereBetween('t.tanggal', [$startDate, $endDate])
                ->sum('t.total');
        case 'BPJS_JAMINAN':
            $bpjs = DB::table('pendapatan_bpjs as t')
                ->join('revenue_masters as rm', 't.revenue_master_id', '=', 'rm.id')
                ->where('rm.tahun', $tahun)
                ->whereIn('rm.is_posted', [0, 1])
                ->whereBetween('t.tanggal', [$startDate, $endDate])
                ->sum('t.total');
            $jam = DB::table('pendapatan_jaminan as t')
                ->join('revenue_masters as rm', 't.revenue_master_id', '=', 'rm.id')
                ->where('rm.tahun', $tahun)
                ->whereIn('rm.is_posted', [0, 1])
                ->whereBetween('t.tanggal', [$startDate, $endDate])
                ->sum('t.total');
            $ded = DB::table('penyesuaian_pendapatans')->whereIn('kategori', ['BPJS', 'JAMINAN'])->whereBetween('tanggal', [$startDate, $endDate])->where('tahun', $tahun)->sum(DB::raw('IFNULL(potongan, 0) + IFNULL(administrasi_bank, 0)'));
            return ($bpjs + $jam) - $ded;
        case 'KERJASAMA':
            return DB::table('pendapatan_kerjasama as t')
                ->join('revenue_masters as rm', 't.revenue_master_id', '=', 'rm.id')
                ->where('rm.tahun', $tahun)
                ->whereIn('rm.is_posted', [0, 1])
                ->whereBetween('t.tanggal', [$startDate, $endDate])
                ->sum('t.total');
        default:
            return 0;
    }
}

$cats = [
    'PASIEN_UMUM' => ['kode' => '4.1.02.01.001.00005', 'nama' => 'Retribusi Pelayanan Kesehatan Pasien Non Jaminan (Mandiri)'],
    'BPJS_JAMINAN' => ['kode' => '4.1.02.01.001.00005', 'nama' => 'Retribusi Pelayanan Kesehatan Pasien Jaminan'],
    'KERJASAMA' => ['kode' => '4.1.02.02.001.00005', 'nama' => 'Retribusi Pemakaian Ruangan'],
];

foreach($cats as $k => $v) {
    echo "$k: " . calculateRealisasiRevenue($k, 2026, $start, $end) . "\n";
}

$exp = DB::table('expenditures')
    ->join('kode_rekening', 'expenditures.kode_rekening_id', '=', 'kode_rekening.id')
    ->whereBetween('expenditures.spending_date', [$start, $end])
    ->select('kode_rekening.kode as kode_rekening', 'kode_rekening.nama as uraian', DB::raw('SUM(expenditures.gross_value) as jumlah'))
    ->groupBy('kode_rekening.kode', 'kode_rekening.nama')
    ->get();

print_r($exp);
