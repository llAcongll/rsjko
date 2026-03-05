<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportService
{
    protected $numberingService;

    public function __construct(NumberingService $numberingService)
    {
        $this->numberingService = $numberingService;
    }
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
            $stats = $this->getActiveRevenueQuery($table)
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
                $gross = $this->getActiveRevenueQuery($table)->whereBetween('tanggal', [$start, $end])->where('tahun', $tahun)->sum('total');
                $tableDeductions[$table] = [
                    'gross' => $gross,
                    'deduction' => $totalDed
                ];
            }
        }

        $roomStats = [];
        $roomPatientStats = [];

        foreach ($tables as $table) {
            $rooms = $this->getActiveRevenueQuery($table)
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
                $query = $this->getActiveRevenueQuery('pendapatan_umum');
                break;
            case 'BPJS_JAMINAN':
                $bpjs = $this->getStatsFromQuery($this->getActiveRevenueQuery('pendapatan_bpjs')->where('tahun', $tahun)->whereBetween('tanggal', [$start, $end]));
                $jam = $this->getStatsFromQuery($this->getActiveRevenueQuery('pendapatan_jaminan')->where('tahun', $tahun)->whereBetween('tanggal', [$start, $end]));

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
                $query = $this->getActiveRevenueQuery('pendapatan_kerjasama');
                break;
            case 'PKL':
                $query = $this->getActiveRevenueQuery('pendapatan_lain')->where(fn($q) => $q->where('transaksi', 'like', '%PKL%')->orWhere('transaksi', 'like', '%Praktek Kerja Lapangan%'));
                break;
            case 'MAGANG':
                $query = $this->getActiveRevenueQuery('pendapatan_lain')->where('transaksi', 'like', '%Magang%');
                break;
            case 'PENELITIAN':
                $query = $this->getActiveRevenueQuery('pendapatan_lain')->where('transaksi', 'like', '%Penelitian%');
                break;
            case 'PERMINTAAN_DATA':
                $query = $this->getActiveRevenueQuery('pendapatan_lain')->where('transaksi', 'like', '%Permintaan Data%');
                break;
            case 'STUDY_BANDING':
                $query = $this->getActiveRevenueQuery('pendapatan_lain')->where('transaksi', 'like', '%Study Banding%');
                break;
            case 'LAIN_LAIN':
                $query = $this->getActiveRevenueQuery('pendapatan_lain')
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
        $query = DB::table('expenditures')
            ->join('kode_rekening', 'expenditures.kode_rekening_id', '=', 'kode_rekening.id')
            ->whereYear('expenditures.spending_date', $tahun)
            ->whereBetween('expenditures.spending_date', [$start, $end]);

        $details = (clone $query)
            ->select(
                'kode_rekening.kode',
                'kode_rekening.nama',
                DB::raw('SUM(gross_value) as total'),
                DB::raw("SUM(CASE WHEN spending_type = 'UP' THEN gross_value ELSE 0 END) as up"),
                DB::raw("0 as gu"), // GU is a cash flow, not an economic event category here
                DB::raw("SUM(CASE WHEN spending_type = 'LS' THEN gross_value ELSE 0 END) as ls")
            )
            ->groupBy('kode_rekening.kode', 'kode_rekening.nama')
            ->orderBy('kode_rekening.kode')
            ->get();

        $summary = DB::table('expenditures')
            ->join('kode_rekening', 'expenditures.kode_rekening_id', '=', 'kode_rekening.id')
            ->whereYear('spending_date', $tahun)
            ->whereBetween('spending_date', [$start, $end])
            ->select(
                'kode_rekening.sumber_data as kategori',
                DB::raw('SUM(gross_value) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('kode_rekening.sumber_data')
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
            $data = $this->getActiveRevenueQuery($table)
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
        $namaBulan = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];

        for ($i = 1; $i <= 12; $i++) {
            $bank = (float) ($rekKoran[$i] ?? 0);
            $pend = (float) ($revenues[$i] ?? 0);
            $diff = $bank - $pend;

            $keterangan = 'Data Cocok (Match)';
            if (abs($diff) > 0.1) {
                // Find transactions from bank that don't have a matching record in income modules
                $tables = ['pendapatan_umum', 'pendapatan_bpjs', 'pendapatan_jaminan', 'pendapatan_kerjasama', 'pendapatan_lain'];

                $unmatchedTransactions = [];
                $bankRecords = DB::table('rekening_korans')
                    ->whereYear('tanggal', $tahun)
                    ->whereMonth('tanggal', $i)
                    ->where('cd', 'C')
                    ->orderBy('tanggal', 'asc')
                    ->get();

                foreach ($bankRecords as $bankItem) {
                    $hasMatch = false;
                    foreach ($tables as $table) {
                        if ($this->getActiveRevenueQuery($table)->where('tanggal', $bankItem->tanggal)->where('total', $bankItem->jumlah)->exists()) {
                            $hasMatch = true;
                            break;
                        }
                    }

                    if (!$hasMatch) {
                        $bankName = ($bankItem->bank == 'Bank Riau Kepri Syariah' || $bankItem->bank == 'BRK') ? 'BRK' : (($bankItem->bank == 'Bank Syariah Indonesia' || $bankItem->bank == 'BSI') ? 'BSI' : $bankItem->bank);
                        $tgl = \Carbon\Carbon::parse($bankItem->tanggal)->format('d/m');
                        // Clean up description: remove common noise or keep first 50 chars
                        $rawDesc = $bankItem->keterangan;
                        $desc = (strlen($rawDesc) > 40) ? substr($rawDesc, 0, 37) . '...' : $rawDesc;

                        $unmatchedTransactions[] = "{$bankName} {$tgl} [Rp " . number_format($bankItem->jumlah, 0, ',', '.') . "] - {$desc}";
                        if (count($unmatchedTransactions) >= 3)
                            break;
                    }
                }

                if (!empty($unmatchedTransactions)) {
                    $keterangan = "Belum Tercatat:<br>";
                    foreach ($unmatchedTransactions as $ut) {
                        $keterangan .= "• {$ut}<br>";
                    }
                    if (count($unmatchedTransactions) >= 3) {
                        $keterangan .= "...";
                    }
                } else {
                    $keterangan = "Sisa selisih harian:<br>Rp " . number_format(abs($diff), 0, ',', '.');
                }
            }

            $rekonData[] = (object) [
                'tanggal' => $namaBulan[$i],
                'bank' => $bank,
                'pendapatan' => $pend,
                'selisih' => $diff,
                'keterangan' => $keterangan
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
                DB::raw("SUM(CASE WHEN tahun_piutang < $tahun " . ($end ? "AND tanggal <= '$end'" : "") . " THEN pelunasan ELSE 0 END) as sa_cash"),
                DB::raw("SUM(CASE WHEN tahun_piutang < $tahun " . ($end ? "AND tanggal <= '$end'" : "") . " THEN potongan ELSE 0 END) as sa_potongan"),
                DB::raw("SUM(CASE WHEN tahun_piutang < $tahun " . ($end ? "AND tanggal <= '$end'" : "") . " THEN administrasi_bank ELSE 0 END) as sa_adm"),
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
            $item->total_adm = (float) $item->sa_adm + $item->berjalan_adm;
            $item->sisa_sa = max(0, $item->sa_piutang - $item->sa_pelunasan);
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
                    'sa_pelunasan' => (float) ($sa_pot + $sa_adm),
                    'sa_potongan' => $sa_pot,
                    'sa_adm' => $sa_adm,
                    'berjalan_piutang' => 0,
                    'berjalan_pelunasan' => (float) ($b_pot + $b_adm),
                    'berjalan_potongan' => $b_pot,
                    'berjalan_adm' => $b_adm,
                    'total_pelunasan' => (float) ($t_clear),
                    'total_potongan' => $sa_pot + $b_pot,
                    'total_adm' => $sa_adm + $b_adm,
                    'sisa_sa' => 0,
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
            'sisa_sa' => $finalData->sum('sisa_sa'),
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
            $this->getActiveRevenueQuery('pendapatan_kerjasama')->join('mous', 'pendapatan_kerjasama.mou_id', '=', 'mous.id')->where('pendapatan_kerjasama.tahun', $tahun)->whereBetween('pendapatan_kerjasama.tanggal', [$start, $end])->select('mous.nama as nama_mou', DB::raw('COUNT(*) as count'), DB::raw('SUM(rs_tindakan + rs_obat) as rs'), DB::raw('SUM(pelayanan_tindakan + pelayanan_obat) as pelayanan'), DB::raw('SUM(total) as total'), DB::raw('0 as potongan'), DB::raw('0 as adm_bank'))->groupBy('mous.nama'),
            $this->getActiveRevenueQuery('pendapatan_lain')->join('mous', 'pendapatan_lain.mou_id', '=', 'mous.id')->where('pendapatan_lain.tahun', $tahun)->whereBetween('pendapatan_lain.tanggal', [$start, $end])->select('mous.nama as nama_mou', DB::raw('COUNT(*) as count'), DB::raw('SUM(rs_tindakan + rs_obat) as rs'), DB::raw('SUM(pelayanan_tindakan + pelayanan_obat) as pelayanan'), DB::raw('SUM(total) as total'), DB::raw('0 as potongan'), DB::raw('0 as adm_bank'))->groupBy('mous.nama'),
            $this->getActiveRevenueQuery('pendapatan_bpjs')->join('perusahaans', 'pendapatan_bpjs.perusahaan_id', '=', 'perusahaans.id')->where('pendapatan_bpjs.tahun', $tahun)->whereBetween('pendapatan_bpjs.tanggal', [$start, $end])->select('perusahaans.nama as nama_mou', DB::raw('COUNT(*) as count'), DB::raw('SUM(rs_tindakan + rs_obat) as rs'), DB::raw('SUM(pelayanan_tindakan + pelayanan_obat) as pelayanan'), DB::raw('SUM(total) as total'), DB::raw('0 as potongan'), DB::raw('0 as adm_bank'))->groupBy('perusahaans.nama'),
            $this->getActiveRevenueQuery('pendapatan_jaminan')->join('perusahaans', 'pendapatan_jaminan.perusahaan_id', '=', 'perusahaans.id')->where('pendapatan_jaminan.tahun', $tahun)->whereBetween('pendapatan_jaminan.tanggal', [$start, $end])->select('perusahaans.nama as nama_mou', DB::raw('COUNT(*) as count'), DB::raw('SUM(rs_tindakan + rs_obat) as rs'), DB::raw('SUM(pelayanan_tindakan + pelayanan_obat) as pelayanan'), DB::raw('SUM(total) as total'), DB::raw('0 as potongan'), DB::raw('0 as adm_bank'))->groupBy('perusahaans.nama'),
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
    public function getAnggaranData($category, $start, $end, $tahun, $requestedLevel = 10)
    {
        $startOfYear = $tahun . '-01-01';
        $prevEnd = Carbon::parse($start)->subDay()->toDateString();

        $query = \App\Models\KodeRekening::with('children')
            ->whereNull('parent_id')
            ->orderBy('category', 'asc')
            ->orderBy('kode');

        $initialRoots = $query->get();
        $actualRoots = collect();

        foreach ($initialRoots as $root) {
            // Bypass the overall hospital unit node to show Pendapatan / Belanja directly
            if (
                str_contains(strtoupper($root->nama), 'RUMAH SAKIT') ||
                str_contains(strtoupper($root->nama), 'RSUD') ||
                str_contains(strtoupper($root->nama), 'RSJKO') ||
                $root->kode === '1.02.0.00.0.00.02.0002'
            ) {

                foreach ($root->children as $child) {
                    if ($category !== 'SEMUA' && $child->category !== $category)
                        continue;
                    $actualRoots->push($child);
                }
            } else {
                if ($category !== 'SEMUA' && $root->category !== $category)
                    continue;
                $actualRoots->push($root);
            }
        }

        $roots = $actualRoots->sortBy(function ($item) {
            return $item->category . '-' . $item->kode;
        })->values();

        $report = [];
        foreach ($roots as $root) {
            $levelShift = $root->level - 1;
            $this->processLraNode($root, $tahun, $start, $end, $startOfYear, $prevEnd, $report, $requestedLevel, $levelShift);
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

    private function processLraNode($node, $tahun, $start, $end, $startOfYear, $prevEnd, &$flatList, $requestedLevel = 10, $levelShift = 0)
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
                    $realLalu = DB::table('expenditures')->where('kode_rekening_id', $node->id)->whereBetween('spending_date', [$startOfYear, $prevEnd])->sum('gross_value');
                $realKini = DB::table('expenditures')->where('kode_rekening_id', $node->id)->whereBetween('spending_date', [$start, $end])->sum('gross_value');
                $realTotal = $realLalu + $realKini;
            }
        } else {
            foreach ($node->children as $child) {
                $res = $this->processLraNode($child, $tahun, $start, $end, $startOfYear, $prevEnd, $childItems, $requestedLevel, $levelShift);
                $target += $res['target'];
                $realLalu += $res['realisasi_lalu'];
                $realKini += $res['realisasi_kini'];
                $realTotal += $res['realisasi_total'];
            }
        }

        $effectiveLevel = max(1, $node->level - $levelShift);
        $item = ['id' => $node->id, 'kode' => $node->kode, 'nama' => $node->nama, 'level' => $effectiveLevel, 'tipe' => $node->tipe, 'category' => $node->category, 'target' => $target, 'realisasi_lalu' => $realLalu, 'realisasi_kini' => $realKini, 'realisasi_total' => $realTotal, 'selisih' => $target - $realTotal, 'persen' => $target > 0 ? round(($realTotal / $target) * 100, 2) : 0];

        if ($effectiveLevel <= $requestedLevel) {
            $flatList[] = $item;
            foreach ($childItems as $ci)
                $flatList[] = $ci;
        }
        return $item;
    }

    private function calculateRealisasiDetail($sumberData, $tahun, $startDate, $endDate, $nodeId = null)
    {
        switch ($sumberData) {
            case 'UMUM':
            case 'PASIEN_UMUM':
                return $this->getActiveRevenueQuery('pendapatan_umum')->where('tahun', $tahun)->whereBetween('tanggal', [$startDate, $endDate])->sum('total');
            case 'BPJS_JAMINAN':
                $bpjs = $this->getActiveRevenueQuery('pendapatan_bpjs')->where('tahun', $tahun)->whereBetween('tanggal', [$startDate, $endDate])->sum('total');
                $jam = $this->getActiveRevenueQuery('pendapatan_jaminan')->where('tahun', $tahun)->whereBetween('tanggal', [$startDate, $endDate])->sum('total');
                $ded = DB::table('penyesuaian_pendapatans')->whereIn('kategori', ['BPJS', 'JAMINAN'])->whereBetween('tanggal', [$startDate, $endDate])->where('tahun', $tahun)->sum(DB::raw('IFNULL(potongan, 0) + IFNULL(administrasi_bank, 0)'));
                return ($bpjs + $jam) - $ded;
            case 'KERJASAMA':
                return $this->getActiveRevenueQuery('pendapatan_kerjasama')->where('tahun', $tahun)->whereBetween('tanggal', [$startDate, $endDate])->sum('total');
            case 'PKL':
                return $this->getActiveRevenueQuery('pendapatan_lain')->where('tahun', $tahun)->whereBetween('tanggal', [$startDate, $endDate])->where(fn($q) => $q->where('transaksi', 'like', '%PKL%')->orWhere('transaksi', 'like', '%Praktek Kerja Lapangan%'))->sum('total');
            case 'MAGANG':
                return $this->getActiveRevenueQuery('pendapatan_lain')->where('tahun', $tahun)->whereBetween('tanggal', [$startDate, $endDate])->where('transaksi', 'like', '%Magang%')->sum('total');
            case 'PENELITIAN':
                return $this->getActiveRevenueQuery('pendapatan_lain')->where('tahun', $tahun)->whereBetween('tanggal', [$startDate, $endDate])->where('transaksi', 'like', '%Penelitian%')->sum('total');
            case 'PERMINTAAN_DATA':
                return $this->getActiveRevenueQuery('pendapatan_lain')->where('tahun', $tahun)->whereBetween('tanggal', [$startDate, $endDate])->where('transaksi', 'like', '%Permintaan Data%')->sum('total');
            case 'STUDY_BANDING':
                return $this->getActiveRevenueQuery('pendapatan_lain')->where('tahun', $tahun)->whereBetween('tanggal', [$startDate, $endDate])->where('transaksi', 'like', '%Study Banding%')->sum('total');
            case 'LAIN_LAIN':
                return $this->getActiveRevenueQuery('pendapatan_lain')->where('tahun', $tahun)->whereBetween('tanggal', [$startDate, $endDate])->where('transaksi', 'NOT LIKE', '%PKL%')->where('transaksi', 'NOT LIKE', '%Praktek Kerja Lapangan%')->where('transaksi', 'NOT LIKE', '%Magang%')->where('transaksi', 'NOT LIKE', '%Penelitian%')->where('transaksi', 'NOT LIKE', '%Permintaan Data%')->where('transaksi', 'NOT LIKE', '%Study Banding%')->sum('total');
            case 'PEGAWAI':
            case 'BARANG_JASA':
            case 'MODAL':
                $q = DB::table('expenditures')
                    ->join('kode_rekening', 'expenditures.kode_rekening_id', '=', 'kode_rekening.id')
                    ->where('kode_rekening.sumber_data', $sumberData)
                    ->whereBetween('expenditures.spending_date', [$startDate, $endDate]);
                if ($nodeId)
                    $q->where('expenditures.kode_rekening_id', $nodeId);
                return $q->sum('expenditures.gross_value');
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

    public function getBkuData($year, $month = null)
    {
        $query = \App\Models\TreasurerCash::whereYear('date', $year);

        $openingBalance = 0;
        if ($month) {
            $lastEntryBefore = \App\Models\TreasurerCash::where('date', '<', \Carbon\Carbon::create($year, $month, 1)->toDateString())
                ->orderBy('date', 'desc')
                ->orderBy('id', 'desc')
                ->first();
            $openingBalance = (float) ($lastEntryBefore->balance ?? 0);
            $query->whereMonth('date', $month);
        }

        $data = $query->orderBy('date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // Get bank entries to map them to BKU rows for accurate column sync
        $bankEntries = \App\Models\BankAccountLedger::whereYear('date', $year)
            ->orderBy('date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $bankMap = [];
        foreach ($bankEntries as $be) {
            // Map by ref for specific rows
            $bankMap[$be->ref_table][$be->ref_id] = (float) $be->balance;
            // Also map by its own ID if ref_table is bank_account_ledgers
            $bankMap['bank_account_ledgers'][$be->id] = (float) $be->balance;
        }

        $expenditureIds = $data->where('ref_table', 'expenditures')->pluck('ref_id')->unique();
        $disbursementIds = $data->where('ref_table', 'fund_disbursements')->pluck('ref_id')->unique();

        $expenditures = \App\Models\Expenditure::with('kodeRekening')->whereIn('id', $expenditureIds)->get()->keyBy('id');
        $disbursements = \App\Models\FundDisbursement::with(['expenditure.kodeRekening', 'kodeRekening'])->whereIn('id', $disbursementIds)->get()->keyBy('id');

        $startDate = $month ? \Carbon\Carbon::create($year, $month, 1)->toDateString() : \Carbon\Carbon::create($year, 1, 1)->toDateString();

        $openingBank = (float) (\App\Models\BankAccountLedger::where('date', '<', $startDate)
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->value('balance') ?? 0);

        $currentBankRunning = $openingBank;

        // Types excluded from Saldo Dana (Tunai): Bank transactions that don't pass through physical cash.
        $excludeFromSaldoDana = [
            'LS_IN',
            'LS_RECEIPT',
            'ACTIVITY_LS',
            'DEPOSIT_LS',
            'BELANJA_LS',
            'DEPOSIT_MANUAL',
            'TRANSFER_PENERIMAAN',
            'SISA_KAS',
            'PENYESUAIAN_SP2D',
            'PENYESUAIAN_REALISASI'
        ];
        $openingSaldoDana = 0;
        if ($month) {
            $beforeEntries = \App\Models\TreasurerCash::where('date', '<', \Carbon\Carbon::create($year, $month, 1)->toDateString())
                ->orderBy('date', 'asc')->orderBy('id', 'asc')->get();
            foreach ($beforeEntries as $be) {
                if (!in_array($be->type, $excludeFromSaldoDana)) {
                    $openingSaldoDana += (float) $be->debit;
                    $openingSaldoDana -= (float) $be->credit;
                }
            }
        }
        $saldoDanaRunning = $openingSaldoDana;

        $data->transform(function ($item) use ($expenditures, $disbursements, $bankMap, &$currentBankRunning, &$saldoDanaRunning, $excludeFromSaldoDana) {
            $item->kode_rekening = '';
            $item->no_bukti = '';
            $item->uraian = $item->description;

            // Categorization
            $item->transfer_penerimaan = 0;
            $item->sp2d_penerimaan = 0;
            $item->realisasi = 0;

            if ($item->ref_table === 'expenditures' && isset($expenditures[$item->ref_id])) {
                $exp = $expenditures[$item->ref_id];
                $item->kode_rekening = $exp->kodeRekening->kode ?? '';
                $item->no_bukti = $exp->no_bukti ?? '';
                $item->uraian = $exp->description ?? $item->description;
            } elseif ($item->ref_table === 'fund_disbursements' && isset($disbursements[$item->ref_id])) {
                $disb = $disbursements[$item->ref_id];
                $item->uraian = $disb->description ?? $item->description;
                if ($disb->expenditure) {
                    $item->kode_rekening = $disb->expenditure->kodeRekening->kode ?? '';
                } else {
                    $item->kode_rekening = $disb->kodeRekening->kode ?? '';
                }
                // No bukti only for non-penerimaan disbursement types (not used here)
                $item->no_bukti = '';
            }

            // Determine if it's an activity (SPP-based) or just a fund refill
            $isActivity = str_contains($item->type, 'ACTIVITY');
            if (!$isActivity && $item->ref_table === 'fund_disbursements' && isset($disbursements[$item->ref_id])) {
                $isActivity = !empty($disbursements[$item->ref_id]->spp_no);
            }

            if ($item->debit > 0) {
                // Determine if it goes to Pengajuan or Transfer column
                if (str_starts_with($item->type, 'AJU_') || $item->type === 'PENYESUAIAN_SP2D') {
                    $item->sp2d_penerimaan = (float) $item->debit;
                } elseif (in_array($item->type, ['TERIMA_UP', 'GU', 'UP', 'LS_RECEIPT', 'LS_IN', 'DEPOSIT_LS', 'ACTIVITY_LS'])) {
                    // Legacy types or LS — all SP2D-related inflows
                    $item->sp2d_penerimaan = (float) $item->debit;
                } elseif (in_array($item->type, ['TRANSFER_PENERIMAAN', 'SISA_KAS'])) {
                    $item->transfer_penerimaan = (float) $item->debit;
                } else {
                    $item->transfer_penerimaan = (float) $item->debit;
                }
            }

            if ($item->credit > 0) {
                // All expenditures (Credit) go to Realisasi
                $item->realisasi = (float) $item->credit;
            }

            // Update Saldo Dana running balance (UP/GU only, exclude LS)
            if (!in_array($item->type, $excludeFromSaldoDana)) {
                $saldoDanaRunning += (float) $item->debit;
                $saldoDanaRunning -= (float) $item->credit;
            }

            // Sync Bank Balance with Rekening Koran specifically for this row
            if (isset($bankMap[$item->ref_table][$item->ref_id])) {
                // If this BKU row has a direct bank entry link, use that balance
                $currentBankRunning = $bankMap[$item->ref_table][$item->ref_id];
            }

            $item->saldo_bank = $currentBankRunning;
            $item->saldo_tunai = $saldoDanaRunning;
            $item->saldo_akhir = $item->saldo_tunai + $item->saldo_bank;

            return $item;
        });

        $finalSaldoDana = $data->last() ? $data->last()->saldo_tunai : $openingSaldoDana;
        $finalBank = $data->last() ? $data->last()->saldo_bank : $openingBank;
        $finalBku = $finalSaldoDana + $finalBank;

        // Get bank-specific balances
        $bankLedgerService = app(\App\Services\BankLedgerService::class);
        $finalBankBrk = $bankLedgerService->getCurrentBalance('BRK');
        $finalBankBsi = $bankLedgerService->getCurrentBalance('BSI');

        // Calculate Cumulative (YTD) totals
        $endDate = $month ? \Carbon\Carbon::create($year, $month, 1)->endOfMonth()->toDateString() : \Carbon\Carbon::create($year, 12, 31)->toDateString();

        $sp2dTypes = ['TERIMA_UP', 'GU', 'UP', 'LS_RECEIPT', 'LS_IN', 'DEPOSIT_LS', 'ACTIVITY_LS', 'PENYESUAIAN_SP2D'];
        $ytdSp2d = \App\Models\TreasurerCash::whereYear('date', $year)
            ->where('date', '<=', $endDate)
            ->where(function ($q) use ($sp2dTypes) {
                $q->whereIn('type', $sp2dTypes)
                    ->orWhere('type', 'like', 'AJU_%');
            })
            ->sum('debit');

        $ytdExpenditures = \App\Models\TreasurerCash::whereYear('date', $year)
            ->where('date', '<=', $endDate)
            ->sum('credit');

        return [
            'data' => $data,
            'opening_balance' => $openingSaldoDana + $openingBank,
            'opening_bank' => $openingBank,
            'opening_saldo_dana' => $openingSaldoDana,
            'summary' => [
                'total_debit_transfer' => (float) $data->sum('transfer_penerimaan'),
                'total_debit_sp2d' => (float) $data->sum('sp2d_penerimaan'),
                'total_credit_realisasi' => (float) $data->sum('realisasi'),
                'ytd_receipts' => (float) $ytdSp2d,
                'ytd_expenditures' => (float) $ytdExpenditures,
                'final_bank' => $finalBank,
                'final_bank_brk' => $finalBankBrk,
                'final_bank_bsi' => $finalBankBsi,
                'final_tunai' => $finalSaldoDana,
                'final_balance' => $finalBku
            ],
            'period' => $month ? \Carbon\Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y') : $year
        ];
    }

    private function emptyStats()
    {
        return [
            'jasa' => ['RS' => 0, 'PELAYANAN' => 0, 'TOTAL' => 0],
            'payments' => ['TUNAI' => 0, 'NON_TUNAI' => 0, 'TOTAL' => 0],
            'banks' => ['BRK' => 0, 'BSI' => 0, 'TOTAL' => 0]
        ];
    }

    public function getActiveRevenueQuery($table)
    {
        return DB::table($table)->whereExists(function ($query) use ($table) {
            $query->select(DB::raw(1))
                ->from('revenue_masters')
                ->whereColumn('revenue_masters.id', "{$table}.revenue_master_id")
                ->where('revenue_masters.is_posted', true);
        });
    }
}
