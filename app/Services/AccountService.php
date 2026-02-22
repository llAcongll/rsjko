<?php

namespace App\Services;

use App\Models\KodeRekening;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AccountService
{
    protected $realisasiCache = [];
    protected $anggaranCache = [];

    /**
     * Build the hierarchical tree of accounts.
     */
    public function buildTree($category = 'PENDAPATAN', $tahun = null)
    {
        $this->clearCache();

        $rootNodes = KodeRekening::whereNull('parent_id')
            ->where('category', $category)
            ->where('is_active', true)
            ->orderBy('kode')
            ->with([
                'children' => function ($q) {
                    $q->where('is_active', true);
                }
            ])
            ->get();

        if ($tahun) {
            return $rootNodes->map(fn($node) => $this->mapTreeWithTotals($node, $tahun));
        }

        return $rootNodes;
    }

    /**
     * Clear the internal cache for totals.
     */
    public function clearCache()
    {
        $this->realisasiCache = [];
        $this->anggaranCache = [];
    }

    /**
     * Map tree node with calculated totals (Recursive).
     */
    public function mapTreeWithTotals($node, $tahun)
    {
        $totalAnggaran = $this->calculateTotalAnggaran($node, $tahun);
        $totalRealisasi = $this->calculateRealisasi($node, $tahun);

        $children = $node->children->map(fn($child) => $this->mapTreeWithTotals($child, $tahun));

        return [
            'id' => $node->id,
            'kode' => $node->kode,
            'nama' => $node->nama,
            'tipe' => $node->tipe,
            'category' => $node->category,
            'parent_id' => $node->parent_id,
            'sumber_data' => $node->sumber_data,
            'anggaran' => (float) $totalAnggaran,
            'realisasi' => (float) $totalRealisasi,
            'total_anggaran' => (float) $totalAnggaran,
            'total_realisasi' => (float) $totalRealisasi,
            'sisa' => (float) ($totalAnggaran - $totalRealisasi),
            'persen' => $totalAnggaran > 0 ? round(($totalRealisasi / $totalAnggaran) * 100, 2) : 0,
            'children' => $children
        ];
    }

    /**
     * Calculate total original budget for a node and its children.
     */
    public function calculateTotalAnggaran($node, $tahun)
    {
        $cacheKey = "anggaran_{$node->id}_{$tahun}";
        if (isset($this->anggaranCache[$cacheKey]))
            return $this->anggaranCache[$cacheKey];

        if ($node->tipe === 'detail') {
            $value = $node->anggaran()->where('tahun', $tahun)->value('nilai') ?? 0;
            return $this->anggaranCache[$cacheKey] = (float) $value;
        }

        $total = 0;
        foreach ($node->children as $child) {
            $total += $this->calculateTotalAnggaran($child, $tahun);
        }

        return $this->anggaranCache[$cacheKey] = $total;
    }

    /**
     * Calculate total realization for a node and its children.
     */
    public function calculateRealisasi($node, $tahun)
    {
        $cacheKey = "realisasi_{$node->id}_{$tahun}";
        if (isset($this->realisasiCache[$cacheKey]))
            return $this->realisasiCache[$cacheKey];

        if ($node->tipe === 'detail') {
            $totalAmount = $this->getRawRealisasibySource($node, $tahun);
            return $this->realisasiCache[$cacheKey] = (float) $totalAmount;
        }

        $total = 0;
        foreach ($node->children as $child) {
            $total += $this->calculateRealisasi($child, $tahun);
        }

        return $this->realisasiCache[$cacheKey] = $total;
    }

    /**
     * Get raw realization from specific data sources.
     */
    protected function getRawRealisasibySource($node, $tahun)
    {
        if ($node->category === 'PENDAPATAN' && $node->sumber_data) {
            // Internal caching for global sources to avoid repeated DB hits
            $globalCacheKey = "source_{$node->sumber_data}_{$tahun}";
            if (isset($this->realisasiCache[$globalCacheKey]))
                return $this->realisasiCache[$globalCacheKey];

            $total = 0;
            switch ($node->sumber_data) {
                case 'PASIEN_UMUM':
                    $total = DB::table('pendapatan_umum')->where('tahun', $tahun)->sum('total');
                    break;
                case 'BPJS_JAMINAN':
                    $bpjs = DB::table('pendapatan_bpjs')->where('tahun', $tahun)->sum('total');
                    $jam = DB::table('pendapatan_jaminan')->where('tahun', $tahun)->sum('total');
                    $total = $bpjs + $jam;
                    break;
                case 'KERJASAMA':
                    $total = DB::table('pendapatan_kerjasama')->where('tahun', $tahun)->sum('total');
                    break;
                case 'PKL':
                    $total = DB::table('pendapatan_lain')->where('tahun', $tahun)
                        ->where(fn($q) => $q->where('transaksi', 'like', '%PKL%')->orWhere('transaksi', 'like', '%Praktek Kerja Lapangan%'))->sum('total');
                    break;
                case 'MAGANG':
                    $total = DB::table('pendapatan_lain')->where('tahun', $tahun)->where('transaksi', 'like', '%Magang%')->sum('total');
                    break;
                case 'PENELITIAN':
                    $total = DB::table('pendapatan_lain')->where('tahun', $tahun)->where('transaksi', 'like', '%Penelitian%')->sum('total');
                    break;
                case 'PERMINTAAN_DATA':
                    $total = DB::table('pendapatan_lain')->where('tahun', $tahun)->where('transaksi', 'like', '%Permintaan Data%')->sum('total');
                    break;
                case 'STUDY_BANDING':
                    $total = DB::table('pendapatan_lain')->where('tahun', $tahun)->where('transaksi', 'like', '%Study Banding%')->sum('total');
                    break;
                case 'LAIN_LAIN':
                    $total = DB::table('pendapatan_lain')->where('tahun', $tahun)
                        ->where('transaksi', 'NOT LIKE', '%PKL%')
                        ->where('transaksi', 'NOT LIKE', '%Praktek Kerja Lapangan%')
                        ->where('transaksi', 'NOT LIKE', '%Magang%')
                        ->where('transaksi', 'NOT LIKE', '%Penelitian%')
                        ->where('transaksi', 'NOT LIKE', '%Permintaan Data%')
                        ->where('transaksi', 'NOT LIKE', '%Study Banding%')
                        ->sum('total');
                    break;
            }
            return $this->realisasiCache[$globalCacheKey] = $total;
        }

        if ($node->category === 'PENGELUARAN') {
            $query = DB::table('pengeluaran')
                ->where('kode_rekening_id', $node->id)
                ->whereYear('tanggal', $tahun);

            if ($node->sumber_data) {
                $query->where('kategori', $node->sumber_data);
            }

            return (float) $query->sum('nominal');
        }

        return 0;
    }
}
