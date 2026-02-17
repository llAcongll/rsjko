<?php
$p = DB::table('piutangs')->where('perusahaan_id', 3)->where('tahun', 2026)->select(DB::raw('DATE(tanggal) as tgl'), DB::raw('SUM(jumlah_piutang) as total'))->groupBy('tgl')->get()->pluck('total', 'tgl');
$b = DB::table('pendapatan_bpjs')->where('tahun', 2026)->select(DB::raw('DATE(tanggal) as tgl'), DB::raw('SUM(total) as total'))->groupBy('tgl')->get()->pluck('total', 'tgl');
$diffs = [];
$allDates = $p->keys()->merge($b->keys())->unique();
foreach ($allDates as $d) {
    if (($p[$d] ?? 0) != ($b[$d] ?? 0)) {
        $diffs[$d] = ['p' => (float) ($p[$d] ?? 0), 'b' => (float) ($b[$d] ?? 0), 'diff' => (float) (($p[$d] ?? 0) - ($b[$d] ?? 0))];
    }
}
echo json_encode($diffs);
