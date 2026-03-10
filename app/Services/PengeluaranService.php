<?php

namespace App\Services;

use App\Models\Pengeluaran;
use App\Models\AnggaranRekening;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PengeluaranService
{
    protected $siklusService;

    public function __construct(SiklusService $siklusService)
    {
        $this->siklusService = $siklusService;
    }

    /**
     * Check if the nominal exceeds the remaining budget.
     */
    public function checkBudget($kodeRekeningId, $tanggal, $nominal, $excludeId = null)
    {
        $year = date('Y', strtotime($tanggal));

        $anggaran = AnggaranRekening::where('tahun', $year)
            ->where('kode_rekening_id', $kodeRekeningId)
            ->sum('nilai');

        $query = Pengeluaran::whereYear('tanggal', $year)
            ->where('kode_rekening_id', $kodeRekeningId);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $realisasiSaatIni = $query->sum('nominal');
        $sisaAnggaran = $anggaran - $realisasiSaatIni;

        if ($nominal > $sisaAnggaran) {
            return [
                'isValid' => false,
                'sisa' => $sisaAnggaran
            ];
        }

        return ['isValid' => true];
    }

    /**
     * Store a new Pengeluaran record.
     */
    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $data['potongan_pajak'] = $data['potongan_pajak'] ?? 0;
            $data['total_dibayarkan'] = max(0, $data['nominal'] - $data['potongan_pajak']);

            $year = date('Y', strtotime($data['tanggal']));
            $metode = $data['metode_pembayaran'] ?? 'UP';

            if ($metode === 'UP' || $metode === 'GU') {
                $data['siklus_up'] = $this->siklusService->getActiveSiklus($year);
            } else {
                $data['siklus_up'] = 0;
            }

            $pengeluaran = Pengeluaran::create($data);
            $this->syncNumbers($year);

            $pengeluaran->refresh();

            ActivityLog::log(
                'CREATE',
                'PENGELUARAN',
                "Menambah pengeluaran: {$pengeluaran->uraian}",
                $pengeluaran->id,
                null,
                $pengeluaran->toArray()
            );

            return $pengeluaran;
        });
    }

    /**
     * Update an existing Pengeluaran record.
     */
    public function update($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $pengeluaran = Pengeluaran::findOrFail($id);
            $oldValues = $pengeluaran->toArray();

            $oldYear = Carbon::parse($pengeluaran->tanggal)->year;
            $newYear = Carbon::parse($data['tanggal'])->year;

            $data['potongan_pajak'] = $data['potongan_pajak'] ?? 0;
            $data['total_dibayarkan'] = max(0, $data['nominal'] - $data['potongan_pajak']);

            $metode = $data['metode_pembayaran'] ?? $pengeluaran->metode_pembayaran ?? 'UP';
            if (!isset($data['siklus_up'])) {
                if ($metode === 'UP' || $metode === 'GU') {
                    $data['siklus_up'] = $this->siklusService->getActiveSiklus($newYear);
                } else {
                    $data['siklus_up'] = 0;
                }
            }

            $pengeluaran->update($data);

            // Sync numbers for the new year (and old year if it changed)
            $this->syncNumbers($newYear);
            if ($oldYear !== (int) $newYear) {
                $this->syncNumbers($oldYear);
            }

            $pengeluaran->refresh();

            ActivityLog::log(
                'UPDATE',
                'PENGELUARAN',
                "Mengubah pengeluaran: {$pengeluaran->uraian}",
                $pengeluaran->id,
                $oldValues,
                $pengeluaran->toArray()
            );

            return $pengeluaran;
        });
    }

    /**
     * Delete a Pengeluaran record.
     */
    public function delete($id)
    {
        return DB::transaction(function () use ($id) {
            $pengeluaran = Pengeluaran::findOrFail($id);
            $year = Carbon::parse($pengeluaran->tanggal)->year;
            $oldValues = $pengeluaran->toArray();
            $uraian = $pengeluaran->uraian;

            $pengeluaran->delete();
            $this->syncNumbers($year);

            ActivityLog::log(
                'DELETE',
                'PENGELUARAN',
                "Menghapus pengeluaran: {$uraian}",
                $id,
                $oldValues,
                null
            );

            return true;
        });
    }

    /**
     * Synchronize and re-index all numbers for a specific year.
     */
    public function syncNumbers($year)
    {
        $records = Pengeluaran::whereYear('tanggal', $year)
            ->orderBy('tanggal', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $cycleCounters = [];

        foreach ($records as $index => $rec) {
            /** @var Pengeluaran $rec */
            $globalIdx = $index + 1;
            $metode = $rec->metode_pembayaran ?: 'UP';
            $siklus = $rec->siklus_up ?: 0;

            $key = "{$metode}_{$siklus}";
            if (!isset($cycleCounters[$key])) {
                $cycleCounters[$key] = 0;
            }

            $cycleCounters[$key]++;
            $mtdIdx = $cycleCounters[$key];

            $monthRoman = $this->getRoman(Carbon::parse($rec->tanggal)->month);
            $y = Carbon::parse($rec->tanggal)->year;

            $gs = str_pad($globalIdx, 4, '0', STR_PAD_LEFT);
            $ms = str_pad($mtdIdx, 4, '0', STR_PAD_LEFT);

            $rec->no_spp_index = $globalIdx;
            $rec->no_spp_metode_index = $mtdIdx;
            $rec->no_spm_index = $globalIdx;
            $rec->no_spm_metode_index = $mtdIdx;
            $rec->no_sp2d_index = $globalIdx;

            if ($metode === 'GU') {
                $typeLabel = "GU-{$siklus}-{$ms}";
            } elseif ($metode === 'UP') {
                $typeLabel = ($siklus > 1) ? "UP-{$siklus}-{$ms}" : "UP-{$ms}";
            } else {
                $typeLabel = "{$metode}-{$ms}";
            }

            $rec->no_spp = "{$gs}/SPP/{$typeLabel}/BLUD/RSJKO-EHD/{$monthRoman}/{$y}";
            $rec->no_spm = "{$gs}/SPM/{$typeLabel}/BLUD/RSJKO-EHD/{$monthRoman}/{$y}";
            $rec->no_sp2d = "{$gs}/SP2D/1.02.01.03/{$y}";

            $rec->saveQuietly();
        }
    }

    /**
     * Preview the next numbers.
     */
    public function previewNumbers($tanggal, $metode, $id = null)
    {
        $dt = Carbon::parse($tanggal);
        $year = $dt->year;
        $month = $dt->month;
        $monthRoman = $this->getRoman($month);

        $queryBase = Pengeluaran::whereYear('tanggal', $year);
        if ($id) {
            $queryBase->where('id', '!=', $id);
        }

        $globalBefore = (clone $queryBase)
            ->where('tanggal', '<', $tanggal)
            ->count();

        $nextSppGlobal = $globalBefore + 1;

        $metodeBefore = (clone $queryBase)
            ->where('metode_pembayaran', $metode)
            ->where('tanggal', '<', $tanggal)
            ->count();

        $nextSppMetode = $metodeBefore + 1;

        $gspp = str_pad($nextSppGlobal, 4, '0', STR_PAD_LEFT);
        $mspp = str_pad($nextSppMetode, 4, '0', STR_PAD_LEFT);

        $siklus = ($metode === 'UP' || $metode === 'GU') ? $this->siklusService->getActiveSiklus($year) : 0;

        if ($metode === 'GU') {
            $typeLabel = "GU-{$siklus}-{$mspp}";
        } elseif ($metode === 'UP') {
            $typeLabel = ($siklus > 1) ? "UP-{$siklus}-{$mspp}" : "UP-{$mspp}";
        } else {
            $typeLabel = "{$metode}-{$mspp}";
        }

        return [
            'no_spp' => "{$gspp}/SPP/{$typeLabel}/BLUD/RSJKO-EHD/{$monthRoman}/{$year}",
            'no_spm' => "{$gspp}/SPM/{$typeLabel}/BLUD/RSJKO-EHD/{$monthRoman}/{$year}",
            'no_sp2d' => "{$gspp}/SP2D/1.02.01.03/{$year}",
            'indexes' => [
                'spp_index' => $nextSppGlobal,
                'spp_metode_index' => $nextSppMetode,
            ]
        ];
    }

    /**
     * Convert month number to Roman numeral.
     */
    private function getRoman($number)
    {
        $map = [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII'
        ];
        return $map[$number] ?? 'I';
    }
}





