<?php
$tables = ['piutangs', 'pendapatan_umum', 'pendapatan_bpjs', 'pendapatan_jaminan', 'pendapatan_kerjasama', 'pendapatan_lain', 'penyesuaian_pendapatans', 'anggaran_rekening', 'rekening_korans'];
$result = [];
foreach ($tables as $tbl) {
    if (Schema::hasTable($tbl)) {
        $result[$tbl] = DB::select("DESCRIBE $tbl");
    }
}
echo json_encode($result);
