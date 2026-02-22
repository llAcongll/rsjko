<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportService
{
    /**
     * Get revenue summary across all modules
     */
    public function getRevenueSummary($start, $end, $tahun)
    {
        $tables = [
            'UMUM' => 'pendapatan_umum',
            'BPJS' => 'pendapatan_bpjs',
            'JAMINAN' => 'pendapatan_jaminan',
            'KERJASAMA' => 'pendapatan_kerjasama',
            'LAIN' => 'pendapatan_lain'
        ];

        $data = [];
        $totalRs = 0;
        $totalPelayanan = 0;
        $totalAll = 0;

        foreach ($tables as $key => $table) {
            $stats = DB::table($table)
                ->whereBetween('tanggal', [$start, $end])
                ->where('tahun', $tahun)
                ->selectRaw('
                    SUM(IFNULL(rs_tindakan,0) + IFNULL(rs_obat,0)) as rs_total,
                    SUM(IFNULL(pelayanan_tindakan,0) + IFNULL(pelayanan_obat,0)) as pelayanan_total,
                    SUM(IFNULL(total,0)) as grand_total,
                    COUNT(*) as count
                ')
                ->first();

            $rs = $stats->rs_total ?? 0;
            $pelayanan = $stats->pelayanan_total ?? 0;
            $total = $stats->grand_total ?? 0;

            // Apply Deductions for BPJS and JAMINAN
            if ($key === 'BPJS' || $key === 'JAMINAN') {
                $deductions = DB::table('penyesuaian_pendapatans')
                    ->where('kategori', $key)
                    ->whereBetween('tanggal', [$start, $end])
                    ->where('tahun', $tahun)
                    ->selectRaw('SUM(potongan) as total_potongan, SUM(administrasi_bank) as total_adm')
                    ->first();

                $potongan = $deductions->total_potongan ?? 0;
                $adm = $deductions->total_adm ?? 0;

                if ($potongan > 0 || $adm > 0) {
                    $rs -= round($potongan * 0.7, 2);
                    $pelayanan -= round($potongan * 0.3, 2);
                    $rs -= $adm;
                    $total = $rs + $pelayanan;
                }
            }

            $data[$key] = [
                'rs' => $rs,
                'pelayanan' => $pelayanan,
                'total' => $total,
                'count' => (int) ($stats->count ?? 0)
            ];

            $totalRs += $rs;
            $totalPelayanan += $pelayanan;
            $totalAll += $total;
        }

        return [
            'summary' => $data,
            'totals' => [
                'rs' => $totalRs,
                'pelayanan' => $totalPelayanan,
                'grand' => $totalAll
            ]
        ];
    }

    /**
     * Get Room Statistics with Deductions
     */
    public function getRoomStatsWithDeductions($start, $end, $tahun)
    {
        $tables = [
            'UMUM' => 'pendapatan_umum',
            'BPJS' => 'pendapatan_bpjs',
            'JAMINAN' => 'pendapatan_jaminan',
            'KERJASAMA' => 'pendapatan_kerjasama',
            'LAIN' => 'pendapatan_lain'
        ];

        $tableDeductions = [];
        foreach (['BPJS' => 'pendapatan_bpjs', 'JAMINAN' => 'pendapatan_jaminan'] as $key => $table) {
            $ded = DB::table('penyesuaian_pendapatans')
                ->where('kategori', $key)
                ->whereBetween('tanggal', [$start, $end])
                ->where('tahun', $tahun)
                ->selectRaw('SUM(potongan) as total_potongan, SUM(administrasi_bank) as total_adm')
                ->first();

            $totalDed = ($ded->total_potongan ?? 0) + ($ded->total_adm ?? 0);

            if ($totalDed > 0) {
                $gross = DB::table($table)->whereBetween('tanggal', [$start, $end])->where('tahun', $tahun)->sum('total');
                $tableDeductions[$table] = [
                    'gross' => $gross,
                    'deduction' => $totalDed
                ];
            }
        }

        $roomStats = [];
        $roomPatientStats = [];

        foreach ($tables as $table) {
            $rooms = DB::table($table)
                ->join('ruangans', "$table.ruangan_id", '=', 'ruangans.id')
                ->select('ruangans.nama', DB::raw('SUM(total) as gross_total'), DB::raw('COUNT(*) as count'))
                ->whereBetween('tanggal', [$start, $end])
                ->where("$table.tahun", $tahun)
                ->groupBy('ruangans.nama')
                ->get();

            foreach ($rooms as $r) {
                if (!isset($roomStats[$r->nama])) {
                    $roomStats[$r->nama] = ['total' => 0, 'count' => 0];
                }

                $netRoomTotal = $r->gross_total;

                if (isset($tableDeductions[$table]) && $tableDeductions[$table]['gross'] > 0) {
                    $ratio = $r->gross_total / $tableDeductions[$table]['gross'];
                    $roomDed = $tableDeductions[$table]['deduction'] * $ratio;
                    $netRoomTotal -= $roomDed;
                }

                $roomStats[$r->nama]['total'] += $netRoomTotal;
                $roomStats[$r->nama]['count'] += $r->count;

                $roomPatientStats[$r->nama] = ($roomPatientStats[$r->nama] ?? 0) + $r->count;
            }
        }

        uasort($roomStats, fn($a, $b) => $b['total'] <=> $a['total']);

        $flatRoomTotal = [];
        foreach ($roomStats as $nama => $data) {
            $flatRoomTotal[$nama] = $data['total'];
        }

        return [
            'stats' => $roomStats,
            'flat_total' => $flatRoomTotal,
            'flat_count' => $roomPatientStats
        ];
    }

    /**
     * Get detailed breakdown by category
     */
    public function getDetailedBreakdown($category, $tahun, $start, $end)
    {
        $query = null;
        $deductions = 0;

        switch ($category) {
            case 'PASIEN_UMUM':
                $query = DB::table('pendapatan_umum');
                break;
            case 'BPJS_JAMINAN':
                $bpjs = $this->getStatsFromQuery(DB::table('pendapatan_bpjs')->where('tahun', $tahun)->whereBetween('tanggal', [$start, $end]));
                $jam = $this->getStatsFromQuery(DB::table('pendapatan_jaminan')->where('tahun', $tahun)->whereBetween('tanggal', [$start, $end]));

                $ded = DB::table('penyesuaian_pendapatans')
                    ->whereIn('kategori', ['BPJS', 'JAMINAN'])
                    ->whereBetween('tanggal', [$start, $end])
                    ->where('tahun', $tahun)
                    ->selectRaw('SUM(potongan) as total_potongan, SUM(administrasi_bank) as total_adm')
                    ->first();
                $deductions = [
                    'potongan' => $ded->total_potongan ?? 0,
                    'adm' => $ded->total_adm ?? 0
                ];

                return $this->mergeStats($bpjs, $jam, $deductions);

            case 'KERJASAMA':
                $query = DB::table('pendapatan_kerjasama');
                break;
            case 'PKL':
                $query = DB::table('pendapatan_lain')->where(fn($q) => $q->where('transaksi', 'like', '%PKL%')->orWhere('transaksi', 'like', '%Praktek Kerja Lapangan%'));
                break;
            case 'MAGANG':
                $query = DB::table('pendapatan_lain')->where('transaksi', 'like', '%Magang%');
                break;
            case 'PENELITIAN':
                $query = DB::table('pendapatan_lain')->where('transaksi', 'like', '%Penelitian%');
                break;
            case 'PERMINTAAN_DATA':
                $query = DB::table('pendapatan_lain')->where('transaksi', 'like', '%Permintaan Data%');
                break;
            case 'STUDY_BANDING':
                $query = DB::table('pendapatan_lain')->where('transaksi', 'like', '%Study Banding%');
                break;
            case 'LAIN_LAIN':
                $query = DB::table('pendapatan_lain')
                    ->where('transaksi', 'NOT LIKE', '%PKL%')
                    ->where('transaksi', 'NOT LIKE', '%Praktek Kerja Lapangan%')
                    ->where('transaksi', 'NOT LIKE', '%Magang%')
                    ->where('transaksi', 'NOT LIKE', '%Penelitian%')
                    ->where('transaksi', 'NOT LIKE', '%Permintaan Data%')
                    ->where('transaksi', 'NOT LIKE', '%Study Banding%');
                break;
        }

        if ($query) {
            return $this->getStatsFromQuery($query->where('tahun', $tahun)->whereBetween('tanggal', [$start, $end]));
        }

        return $this->emptyStats();
    }

    /**
     * Get Pengeluaran summary and details
     */
    public function getPengeluaranSummary($start, $end, $tahun)
    {
        $query = DB::table('pengeluaran')
            ->join('kode_rekening', 'pengeluaran.kode_rekening_id', '=', 'kode_rekening.id')
            ->whereYear('pengeluaran.tanggal', $tahun)
            ->whereBetween('pengeluaran.tanggal', [$start, $end]);

        $details = (clone $query)
            ->select(
                'kode_rekening.kode',
                'kode_rekening.nama',
                DB::raw('SUM(nominal) as total'),
                DB::raw("SUM(CASE WHEN metode_pembayaran = 'UP' THEN nominal ELSE 0 END) as up"),
                DB::raw("SUM(CASE WHEN metode_pembayaran = 'GU' THEN nominal ELSE 0 END) as gu"),
                DB::raw("SUM(CASE WHEN metode_pembayaran = 'LS' THEN nominal ELSE 0 END) as ls")
            )
            ->groupBy('kode_rekening.kode', 'kode_rekening.nama')
            ->orderBy('kode_rekening.kode')
            ->get();

        $summary = DB::table('pengeluaran')
            ->whereYear('tanggal', $tahun)
            ->whereBetween('tanggal', [$start, $end])
            ->select(
                'kategori',
                DB::raw('SUM(nominal) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('kategori')
            ->get()
            ->keyBy('kategori');

        return [
            'data' => $details,
            'summary' => $summary
        ];
    }

    /**
     * Get DPA (Dokumen Pelaksanaan Anggaran) Data
     */
    public function getDpaData($tahun)
    {
        $rootNodes = \App\Models\KodeRekening::whereNull('parent_id')
            ->orderBy('kode')
            ->get();

        $flatList = [];
        foreach ($rootNodes as $node) {
            $this->processDpaNode($node, $tahun, $flatList);
        }

        return $flatList;
    }

    private function processDpaNode($node, $tahun, &$flatList)
    {
        $total = 0;
        $childList = [];

        if ($node->tipe === 'detail') {
            $rincian = DB::table('anggaran_rincian')
                ->join('anggaran_rekening', 'anggaran_rincian.anggaran_rekening_id', '=', 'anggaran_rekening.id')
                ->where('anggaran_rekening.kode_rekening_id', $node->id)
                ->where('anggaran_rekening.tahun', $tahun)
                ->select(
                    'anggaran_rincian.uraian',
                    'anggaran_rincian.volume',
                    'anggaran_rincian.satuan',
                    'anggaran_rincian.tarif',
                    'anggaran_rincian.subtotal'
                )
                ->get();

            foreach ($rincian as $r) {
                $total += $r->subtotal;
                $childList[] = (object) [
                    'tipe' => 'rincian',
                    'kode_rekening' => '',
                    'uraian' => $r->uraian,
                    'volume' => $r->volume,
                    'satuan' => $r->satuan,
                    'tarif' => $r->tarif,
                    'subtotal' => $r->subtotal,
                    'level' => $node->level + 1
                ];
            }
        } else {
            foreach ($node->children as $child) {
                $res = $this->processDpaNode($child, $tahun, $childList);
                $total += $res['total'];
            }
        }

        $item = (object) [
            'tipe' => 'header',
            'kode_rekening' => $node->kode,
            'uraian' => $node->nama,
            'volume' => null,
            'satuan' => null,
            'tarif' => 0,
            'subtotal' => $total,
            'level' => $node->level
        ];

        if ($total > 0) {
            $flatList[] = $item;
            foreach ($childList as $cl) {
                $flatList[] = $cl;
            }
        }

        return ['total' => $total];
    }

    /**
     * Get Rekon (Rekonsiliasi Bank vs Pendapatan)
     */
    public function getRekonData($tahun)
    {
        $rekKoran = DB::table('rekening_korans')
            ->where('tahun', $tahun)
            ->where('cd', 'C')
            ->select(DB::raw('MONTH(tanggal) as bulan'), DB::raw('SUM(jumlah) as total'))
            ->groupBy('bulan')
            ->get()
            ->pluck('total', 'bulan');

        $tables = ['pendapatan_umum', 'pendapatan_bpjs', 'pendapatan_jaminan', 'pendapatan_kerjasama', 'pendapatan_lain'];
        $revenues = [];
        foreach ($tables as $table) {
            $data = DB::table($table)
                ->where('tahun', $tahun)
                ->select(DB::raw('MONTH(tanggal) as bulan'), DB::raw('SUM(total) as total'))
                ->groupBy('bulan')
                ->get();
            foreach ($data as $d) {
                $revenues[$d->bulan] = ($revenues[$d->bulan] ?? 0) + $d->total;
            }
        }

        $deductions = DB::table('penyesuaian_pendapatans')
            ->where('tahun', $tahun)
            ->select(DB::raw('MONTH(tanggal) as bulan'), DB::raw('SUM(IFNULL(potongan, 0) + IFNULL(administrasi_bank, 0)) as total_ded'))
            ->groupBy('bulan')
            ->get()
            ->pluck('total_ded', 'bulan');

        foreach ($deductions as $bulan => $totalDed) {
            $revenues[$bulan] = ($revenues[$bulan] ?? 0) - $totalDed;
        }

        $rekonData = [];
        $cumulativeDiff = 0;
        $namaBulan = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];

        for ($i = 1; $i <= 12; $i++) {
            $bank = (float) ($rekKoran[$i] ?? 0);
            $pend = (float) ($revenues[$i] ?? 0);
            $diff = $bank - $pend;
            $cumulativeDiff += $diff;

            $rekonData[] = [
                'tanggal' => $namaBulan[$i],
                'bank' => $bank,
                'pendapatan' => $pend,
                'selisih' => $diff,
                'kumulatif' => $cumulativeDiff
            ];
        }

        return $rekonData;
    }

    /**
     * Get Piutang Data
     */
    public function getPiutangData($start, $end, $tahun)
    {
        $piutangData = DB::table('piutangs')
            ->join('perusahaans', 'piutangs.perusahaan_id', '=', 'perusahaans.id')
            ->select(
                'piutangs.perusahaan_id',
                'perusahaans.nama as nama_perusahaan',
                DB::raw("SUM(CASE WHEN tahun < $tahun THEN jumlah_piutang ELSE 0 END) as sa_piutang"),
                DB::raw("SUM(CASE WHEN tahun < $tahun AND status = 'LUNAS' THEN jumlah_piutang ELSE 0 END) as sa_lunas_gross"),
                DB::raw("SUM(CASE WHEN tahun = $tahun " . ($start && $end ? "AND tanggal >= '$start' AND tanggal <= '$end'" : "") . " THEN jumlah_piutang ELSE 0 END) as berjalan_piutang"),
                DB::raw("SUM(CASE WHEN tahun = $tahun " . ($start && $end ? "AND tanggal >= '$start' AND tanggal <= '$end'" : "") . " AND status = 'LUNAS' THEN jumlah_piutang ELSE 0 END) as berjalan_lunas_gross")
            )
            ->groupBy('piutangs.perusahaan_id', 'perusahaans.nama')
            ->get();

        $penyesuaianData = DB::table('penyesuaian_pendapatans')
            ->select(
                'perusahaan_id',
                DB::raw("SUM(CASE WHEN tahun_piutang < $tahun THEN pelunasan ELSE 0 END) as sa_cash"),
                DB::raw("SUM(CASE WHEN tahun_piutang < $tahun THEN potongan ELSE 0 END) as sa_potongan"),
                DB::raw("SUM(CASE WHEN tahun_piutang < $tahun THEN administrasi_bank ELSE 0 END) as sa_adm"),
                DB::raw("SUM(CASE WHEN tahun_piutang = $tahun " . ($start && $end ? "AND tanggal >= '$start' AND tanggal <= '$end'" : "") . " THEN pelunasan ELSE 0 END) as berjalan_cash"),
                DB::raw("SUM(CASE WHEN tahun_piutang = $tahun " . ($start && $end ? "AND tanggal >= '$start' AND tanggal <= '$end'" : "") . " THEN potongan ELSE 0 END) as berjalan_potongan"),
                DB::raw("SUM(CASE WHEN tahun_piutang = $tahun " . ($start && $end ? "AND tanggal >= '$start' AND tanggal <= '$end'" : "") . " THEN administrasi_bank ELSE 0 END) as berjalan_adm")
            )
            ->groupBy('perusahaan_id')
            ->get()
            ->keyBy('perusahaan_id');

        $finalData = $piutangData->map(function ($item) use ($penyesuaianData) {
            $pen = $penyesuaianData->get($item->perusahaan_id);
            $item->sa_piutang = (float) $item->sa_piutang;
            $item->sa_potongan = (float) ($pen->sa_potongan ?? 0);
            $item->sa_adm = (float) ($pen->sa_adm ?? 0);
            $item->sa_manual_cash = (float) ($pen->sa_cash ?? 0);
            $item->sa_pelunasan = (float) $item->sa_lunas_gross + $item->sa_manual_cash + $item->sa_potongan + $item->sa_adm;
            $item->berjalan_piutang = (float) $item->berjalan_piutang;
            $item->berjalan_potongan = (float) ($pen->berjalan_potongan ?? 0);
            $item->berjalan_adm = (float) ($pen->berjalan_adm ?? 0);
            $item->berjalan_manual_cash = (float) ($pen->berjalan_cash ?? 0);
            $item->berjalan_pelunasan = (float) $item->berjalan_lunas_gross + $item->berjalan_manual_cash + $item->berjalan_potongan + $item->berjalan_adm;
            $item->total_pelunasan = $item->sa_pelunasan + $item->berjalan_pelunasan;
            $item->total_potongan = $item->sa_potongan + $item->berjalan_potongan;
            $item->total_adm = $item->sa_adm + $item->berjalan_adm;
            $item->sisa_2025 = max(0, $item->sa_piutang - $item->sa_pelunasan);
            $item->saldo_akhir = ($item->sa_piutang + $item->berjalan_piutang) - ($item->sa_pelunasan + $item->berjalan_pelunasan);
            return $item;
        });

        foreach ($penyesuaianData as $id => $pen) {
            if (!$finalData->contains('perusahaan_id', $id)) {
                $perusahaan = DB::table('perusahaans')->find($id);
                $sa_pot = (float) $pen->sa_potongan;
                $sa_adm = (float) $pen->sa_adm;
                $b_pot = (float) $pen->berjalan_potongan;
                $b_adm = (float) $pen->berjalan_adm;
                $t_clear = $sa_pot + $sa_adm + $b_pot + $b_adm;

                $finalData->push((object) [
                    'perusahaan_id' => $id,
                    'nama_perusahaan' => $perusahaan->nama ?? 'Unknown',
                    'sa_piutang' => 0,
                    'sa_pelunasan' => -($sa_pot + $sa_adm),
                    'sa_potongan' => $sa_pot,
                    'sa_adm' => $sa_adm,
                    'berjalan_piutang' => 0,
                    'berjalan_pelunasan' => -($b_pot + $b_adm),
                    'berjalan_potongan' => $b_pot,
                    'berjalan_adm' => $b_adm,
                    'total_pelunasan' => -($t_clear),
                    'total_potongan' => $sa_pot + $b_pot,
                    'total_adm' => $sa_adm + $b_adm,
                    'sisa_2025' => 0,
                    'saldo_akhir' => -($t_clear)
                ]);
            }
        }

        $totals = [
            'sa_piutang' => $finalData->sum('sa_piutang'),
            'sa_pelunasan' => $finalData->sum('sa_pelunasan'),
            'sa_potongan' => $finalData->sum('sa_potongan'),
            'sa_adm' => $finalData->sum('sa_adm'),
            'berjalan_piutang' => $finalData->sum('berjalan_piutang'),
            'berjalan_pelunasan' => $finalData->sum('berjalan_pelunasan'),
            'berjalan_potongan' => $finalData->sum('berjalan_potongan'),
            'berjalan_adm' => $finalData->sum('berjalan_adm'),
            'total_pelunasan' => $finalData->sum('total_pelunasan'),
            'total_potongan' => $finalData->sum('total_potongan'),
            'total_adm' => $finalData->sum('total_adm'),
            'sisa_2025' => $finalData->sum('sisa_2025'),
            'saldo_akhir' => $finalData->sum('saldo_akhir'),
        ];

        return ['data' => $finalData, 'totals' => $totals];
    }

    /**
     * Get MOU Report Data
     */
    public function getMouData($start, $end, $tahun)
    {
        $queries = [
            DB::table('pendapatan_kerjasama')->join('mous', 'pendapatan_kerjasama.mou_id', '=', 'mous.id')->where('pendapatan_kerjasama.tahun', $tahun)->whereBetween('pendapatan_kerjasama.tanggal', [$start, $end])->select('mous.nama as nama_mou', DB::raw('COUNT(*) as count'), DB::raw('SUM(rs_tindakan + rs_obat) as rs'), DB::raw('SUM(pelayanan_tindakan + pelayanan_obat) as pelayanan'), DB::raw('SUM(total) as total'), DB::raw('0 as potongan'), DB::raw('0 as adm_bank'))->groupBy('mous.nama'),
            DB::table('pendapatan_lain')->join('mous', 'pendapatan_lain.mou_id', '=', 'mous.id')->where('pendapatan_lain.tahun', $tahun)->whereBetween('pendapatan_lain.tanggal', [$start, $end])->select('mous.nama as nama_mou', DB::raw('COUNT(*) as count'), DB::raw('SUM(rs_tindakan + rs_obat) as rs'), DB::raw('SUM(pelayanan_tindakan + pelayanan_obat) as pelayanan'), DB::raw('SUM(total) as total'), DB::raw('0 as potongan'), DB::raw('0 as adm_bank'))->groupBy('mous.nama'),
            DB::table('pendapatan_bpjs')->join('perusahaans', 'pendapatan_bpjs.perusahaan_id', '=', 'perusahaans.id')->where('pendapatan_bpjs.tahun', $tahun)->whereBetween('pendapatan_bpjs.tanggal', [$start, $end])->select('perusahaans.nama as nama_mou', DB::raw('COUNT(*) as count'), DB::raw('SUM(rs_tindakan + rs_obat) as rs'), DB::raw('SUM(pelayanan_tindakan + pelayanan_obat) as pelayanan'), DB::raw('SUM(total) as total'), DB::raw('0 as potongan'), DB::raw('0 as adm_bank'))->groupBy('perusahaans.nama'),
            DB::table('pendapatan_jaminan')->join('perusahaans', 'pendapatan_jaminan.perusahaan_id', '=', 'perusahaans.id')->where('pendapatan_jaminan.tahun', $tahun)->whereBetween('pendapatan_jaminan.tanggal', [$start, $end])->select('perusahaans.nama as nama_mou', DB::raw('COUNT(*) as count'), DB::raw('SUM(rs_tindakan + rs_obat) as rs'), DB::raw('SUM(pelayanan_tindakan + pelayanan_obat) as pelayanan'), DB::raw('SUM(total) as total'), DB::raw('0 as potongan'), DB::raw('0 as adm_bank'))->groupBy('perusahaans.nama'),
            DB::table('penyesuaian_pendapatans')->join('perusahaans', 'penyesuaian_pendapatans.perusahaan_id', '=', 'perusahaans.id')->where('penyesuaian_pendapatans.tahun', $tahun)->whereBetween('penyesuaian_pendapatans.tanggal', [$start, $end])->select('perusahaans.nama as nama_mou', DB::raw('0 as count'), DB::raw('0 as rs'), DB::raw('0 as pelayanan'), DB::raw('0 as total'), DB::raw('SUM(potongan) as potongan'), DB::raw('SUM(administrasi_bank) as adm_bank'))->groupBy('perusahaans.nama')
        ];

        $combined = $queries[0];
        for ($i = 1; $i < count($queries); $i++) {
            $combined->unionAll($queries[$i]);
        }

        return DB::table(DB::raw("({$combined->toSql()}) as merged"))
            ->mergeBindings($combined)
            ->select('nama_mou', DB::raw('SUM(count) as count'), DB::raw('SUM(rs) as rs'), DB::raw('SUM(pelayanan) as pelayanan'), DB::raw('SUM(total) as gross'), DB::raw('SUM(potongan) as potongan'), DB::raw('SUM(adm_bank) as adm_bank'), DB::raw('SUM(total) - (SUM(potongan) + SUM(adm_bank)) as total'))
            ->groupBy('nama_mou')
            ->orderByDesc('total')
            ->get();
    }

    /**
     * Get Anggaran Data (LRA)
     */
    public function getAnggaranData($category, $start, $end, $tahun)
    {
        $startOfYear = $tahun . '-01-01';
        $prevEnd = Carbon::parse($start)->subDay()->toDateString();

        $query = \App\Models\KodeRekening::with('children')
            ->whereNull('parent_id')
            ->orderBy('category', 'asc')
            ->orderBy('kode');

        if ($category !== 'SEMUA') {
            $query->where('category', $category);
        }

        $roots = $query->get();
        $report = [];
        foreach ($roots as $root) {
            $this->processLraNode($root, $tahun, $start, $end, $startOfYear, $prevEnd, $report);
        }

        $totalTarget = 0;
        $totalRealLalu = 0;
        $totalRealKini = 0;
        $totalRealTotal = 0;
        $pendTotals = ['target' => 0, 'real' => 0, 'lalu' => 0, 'kini' => 0];
        $pengTotals = ['target' => 0, 'real' => 0, 'lalu' => 0, 'kini' => 0];

        foreach ($report as $item) {
            if ($roots->contains('id', $item['id'])) {
                if ($item['category'] === 'PENDAPATAN') {
                    $pendTotals['target'] += $item['target'];
                    $pendTotals['real'] += $item['realisasi_total'];
                    $pendTotals['lalu'] += $item['realisasi_lalu'];
                    $pendTotals['kini'] += $item['realisasi_kini'];
                } else {
                    $pengTotals['target'] += $item['target'];
                    $pengTotals['real'] += $item['realisasi_total'];
                    $pengTotals['lalu'] += $item['realisasi_lalu'];
                    $pengTotals['kini'] += $item['realisasi_kini'];
                }

                $multiplier = ($item['category'] === 'PENGELUARAN') ? -1 : 1;
                if ($category === 'SEMUA') {
                    $totalTarget += ($item['target'] * $multiplier);
                    $totalRealLalu += ($item['realisasi_lalu'] * $multiplier);
                    $totalRealKini += ($item['realisasi_kini'] * $multiplier);
                    $totalRealTotal += ($item['realisasi_total'] * $multiplier);
                } else {
                    $totalTarget += $item['target'];
                    $totalRealLalu += $item['realisasi_lalu'];
                    $totalRealKini += $item['realisasi_kini'];
                    $totalRealTotal += $item['realisasi_total'];
                }
            }
        }

        $res = [
            'data' => $report,
            'totals' => [
                'target' => $totalTarget,
                'realisasi_lalu' => $totalRealLalu,
                'realisasi_kini' => $totalRealKini,
                'realisasi_total' => $totalRealTotal,
                'persen' => $totalTarget != 0 ? round(($totalRealTotal / abs($totalTarget)) * 100, 2) : 0
            ]
        ];

        if ($category === 'SEMUA') {
            $res['sub_totals'] = [
                'pendapatan' => [
                    'target' => $pendTotals['target'],
                    'real' => $pendTotals['real'],
                    'real_lalu' => $pendTotals['lalu'],
                    'real_kini' => $pendTotals['kini'],
                    'persen' => $pendTotals['target'] > 0 ? round(($pendTotals['real'] / $pendTotals['target']) * 100, 2) : 0
                ],
                'pengeluaran' => [
                    'target' => $pengTotals['target'],
                    'real' => $pengTotals['real'],
                    'real_lalu' => $pengTotals['lalu'],
                    'real_kini' => $pengTotals['kini'],
                    'persen' => $pengTotals['target'] > 0 ? round(($pengTotals['real'] / $pengTotals['target']) * 100, 2) : 0
                ]
            ];
            $res['data_pendapatan'] = array_values(array_filter($report, fn($item) => $item['category'] === 'PENDAPATAN'));
            $res['data_pengeluaran'] = array_values(array_filter($report, fn($item) => $item['category'] === 'PENGELUARAN'));
        }

        return $res;
    }

    private function processLraNode($node, $tahun, $start, $end, $startOfYear, $prevEnd, &$flatList)
    {
        $target = 0;
        $realLalu = 0;
        $realKini = 0;
        $realTotal = 0;
        $childItems = [];

        if ($node->tipe === 'detail') {
            $target = DB::table('anggaran_rekening')->where('kode_rekening_id', $node->id)->where('tahun', $tahun)->value('nilai') ?? 0;
            if ($node->sumber_data) {
                if ($start > $startOfYear)
                    $realLalu = $this->calculateRealisasiDetail($node->sumber_data, $tahun, $startOfYear, $prevEnd, $node->id);
                $realKini = $this->calculateRealisasiDetail($node->sumber_data, $tahun, $start, $end, $node->id);
                $realTotal = $realLalu + $realKini;
            } elseif ($node->category === 'PENGELUARAN') {
                if ($start > $startOfYear)
                    $realLalu = DB::table('pengeluaran')->where('kode_rekening_id', $node->id)->whereBetween('tanggal', [$startOfYear, $prevEnd])->sum('nominal');
                $realKini = DB::table('pengeluaran')->where('kode_rekening_id', $node->id)->whereBetween('tanggal', [$start, $end])->sum('nominal');
                $realTotal = $realLalu + $realKini;
            }
        } else {
            foreach ($node->children as $child) {
                $res = $this->processLraNode($child, $tahun, $start, $end, $startOfYear, $prevEnd, $childItems);
                $target += $res['target'];
                $realLalu += $res['realisasi_lalu'];
                $realKini += $res['realisasi_kini'];
                $realTotal += $res['realisasi_total'];
            }
        }

        $item = ['id' => $node->id, 'kode' => $node->kode, 'nama' => $node->nama, 'level' => $node->level, 'tipe' => $node->tipe, 'category' => $node->category, 'target' => $target, 'realisasi_lalu' => $realLalu, 'realisasi_kini' => $realKini, 'realisasi_total' => $realTotal, 'selisih' => $target - $realTotal, 'persen' => $target > 0 ? round(($realTotal / $target) * 100, 2) : 0];

        $flatList[] = $item;
        foreach ($childItems as $ci)
            $flatList[] = $ci;
        return $item;
    }

    private function calculateRealisasiDetail($sumberData, $tahun, $startDate, $endDate, $nodeId = null)
    {
        switch ($sumberData) {
            case 'PASIEN_UMUM':
                return DB::table('pendapatan_umum')->where('tahun', $tahun)->whereBetween('tanggal', [$startDate, $endDate])->sum('total');
            case 'BPJS_JAMINAN':
                $bpjs = DB::table('pendapatan_bpjs')->where('tahun', $tahun)->whereBetween('tanggal', [$startDate, $endDate])->sum('total');
                $jam = DB::table('pendapatan_jaminan')->where('tahun', $tahun)->whereBetween('tanggal', [$startDate, $endDate])->sum('total');
                $ded = DB::table('penyesuaian_pendapatans')->whereIn('kategori', ['BPJS', 'JAMINAN'])->whereBetween('tanggal', [$startDate, $endDate])->where('tahun', $tahun)->sum(DB::raw('IFNULL(potongan, 0) + IFNULL(administrasi_bank, 0)'));
                return ($bpjs + $jam) - $ded;
            case 'KERJASAMA':
                return DB::table('pendapatan_kerjasama')->where('tahun', $tahun)->whereBetween('tanggal', [$startDate, $endDate])->sum('total');
            case 'PKL':
                return DB::table('pendapatan_lain')->where('tahun', $tahun)->whereBetween('tanggal', [$startDate, $endDate])->where(fn($q) => $q->where('transaksi', 'like', '%PKL%')->orWhere('transaksi', 'like', '%Praktek Kerja Lapangan%'))->sum('total');
            case 'MAGANG':
                return DB::table('pendapatan_lain')->where('tahun', $tahun)->whereBetween('tanggal', [$startDate, $endDate])->where('transaksi', 'like', '%Magang%')->sum('total');
            case 'PENELITIAN':
                return DB::table('pendapatan_lain')->where('tahun', $tahun)->whereBetween('tanggal', [$startDate, $endDate])->where('transaksi', 'like', '%Penelitian%')->sum('total');
            case 'PERMINTAAN_DATA':
                return DB::table('pendapatan_lain')->where('tahun', $tahun)->whereBetween('tanggal', [$startDate, $endDate])->where('transaksi', 'like', '%Permintaan Data%')->sum('total');
            case 'STUDY_BANDING':
                return DB::table('pendapatan_lain')->where('tahun', $tahun)->whereBetween('tanggal', [$startDate, $endDate])->where('transaksi', 'like', '%Study Banding%')->sum('total');
            case 'LAIN_LAIN':
                return DB::table('pendapatan_lain')->where('tahun', $tahun)->whereBetween('tanggal', [$startDate, $endDate])->where('transaksi', 'NOT LIKE', '%PKL%')->where('transaksi', 'NOT LIKE', '%Praktek Kerja Lapangan%')->where('transaksi', 'NOT LIKE', '%Magang%')->where('transaksi', 'NOT LIKE', '%Penelitian%')->where('transaksi', 'NOT LIKE', '%Permintaan Data%')->where('transaksi', 'NOT LIKE', '%Study Banding%')->sum('total');
            case 'PEGAWAI':
            case 'BARANG_JASA':
            case 'MODAL':
                $q = DB::table('pengeluaran')->where('kategori', $sumberData)->whereBetween('tanggal', [$startDate, $endDate]);
                if ($nodeId)
                    $q->where('kode_rekening_id', $nodeId);
                return $q->sum('nominal');
        }
        return 0;
    }

    private function getStatsFromQuery($query)
    {
        $data = $query->select(
            'metode_pembayaran',
            'bank',
            DB::raw('SUM(total) as total'),
            DB::raw('SUM(IFNULL(rs_tindakan, 0) + IFNULL(rs_obat, 0)) as total_rs'),
            DB::raw('SUM(IFNULL(pelayanan_tindakan, 0) + IFNULL(pelayanan_obat, 0)) as total_pelayanan')
        )
            ->groupBy('metode_pembayaran', 'bank')
            ->get();

        $tunai = 0;
        $nonTunai = 0;
        $brk = 0;
        $bsi = 0;
        $rs = 0;
        $pelayanan = 0;

        foreach ($data as $row) {
            $rs += $row->total_rs;
            $pelayanan += $row->total_pelayanan;
            if ($row->metode_pembayaran === 'TUNAI') {
                $tunai += $row->total;
                $brk += $row->total;
            } else {
                $nonTunai += $row->total;
                if ($row->bank === 'BRK' || $row->bank === 'Bank Riau Kepri Syariah') {
                    $brk += $row->total;
                } else if ($row->bank === 'BSI' || $row->bank === 'Bank Syariah Indonesia') {
                    $bsi += $row->total;
                }
            }
        }

        return [
            'jasa' => ['RS' => $rs, 'PELAYANAN' => $pelayanan, 'TOTAL' => $rs + $pelayanan],
            'payments' => ['TUNAI' => $tunai, 'NON_TUNAI' => $nonTunai, 'TOTAL' => $tunai + $nonTunai],
            'banks' => ['BRK' => $brk, 'BSI' => $bsi, 'TOTAL' => $brk + $bsi]
        ];
    }

    private function mergeStats($s1, $s2, $deductions = 0)
    {
        $potongan = is_array($deductions) ? ($deductions['potongan'] ?? 0) : 0;
        $adm = is_array($deductions) ? ($deductions['adm'] ?? 0) : (!is_array($deductions) ? $deductions : 0);
        $totalDed = $potongan + $adm;

        $res = [
            'jasa' => [
                'RS' => $s1['jasa']['RS'] + $s2['jasa']['RS'],
                'PELAYANAN' => $s1['jasa']['PELAYANAN'] + $s2['jasa']['PELAYANAN'],
            ],
            'payments' => [
                'TUNAI' => $s1['payments']['TUNAI'] + $s2['payments']['TUNAI'],
                'NON_TUNAI' => $s1['payments']['NON_TUNAI'] + $s2['payments']['NON_TUNAI'],
            ],
            'banks' => [
                'BRK' => $s1['banks']['BRK'] + $s2['banks']['BRK'],
                'BSI' => $s1['banks']['BSI'] + $s2['banks']['BSI'],
            ]
        ];

        if ($potongan > 0 || $adm > 0) {
            $res['jasa']['RS'] -= round($potongan * 0.7, 2);
            $res['jasa']['PELAYANAN'] -= round($potongan * 0.3, 2);
            $res['jasa']['RS'] -= $adm;
        }

        $res['payments']['NON_TUNAI'] -= $totalDed;
        $res['banks']['BRK'] -= $totalDed;
        $res['jasa']['TOTAL'] = $res['jasa']['RS'] + $res['jasa']['PELAYANAN'];
        $res['payments']['TOTAL'] = $res['payments']['TUNAI'] + $res['payments']['NON_TUNAI'];
        $res['banks']['TOTAL'] = $res['banks']['BRK'] + $res['banks']['BSI'];

        return $res;
    }

    private function emptyStats()
    {
        return [
            'jasa' => ['RS' => 0, 'PELAYANAN' => 0, 'TOTAL' => 0],
            'payments' => ['TUNAI' => 0, 'NON_TUNAI' => 0, 'TOTAL' => 0],
            'banks' => ['BRK' => 0, 'BSI' => 0, 'TOTAL' => 0]
        ];
    }
}
