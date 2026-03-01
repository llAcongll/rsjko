<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RevenueMaster;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use App\Services\RevenueService;

class RevenueMasterController extends Controller
{
    protected $service;

    public function __construct(RevenueService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $category = $request->get('kategori');

        // Authorization based on category
        $permMap = [
            'UMUM' => 'PENDAPATAN_UMUM_VIEW',
            'BPJS' => 'PENDAPATAN_BPJS_VIEW',
            'JAMINAN' => 'PENDAPATAN_JAMINAN_VIEW',
            'LAIN' => 'PENDAPATAN_LAIN_VIEW',
            'KERJASAMA' => 'PENDAPATAN_KERJA_VIEW',
        ];

        if ($category && isset($permMap[$category])) {
            abort_unless(auth()->user()->hasPermission($permMap[$category]) || auth()->user()->hasPermission('MASTER_VIEW'), 403);
        }

        $perPage = $request->get('per_page', 10);
        $search = $request->get('search');

        $query = RevenueMaster::where('tahun', session('tahun_anggaran'))
            ->when($category, function ($q) use ($category) {
                $q->where('kategori', $category);
            })
            ->orderBy('tanggal', 'asc')
            ->orderBy('id', 'asc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $dateSearch = $this->service->parseDate($search) ?? $search;
                $q->whereDate('tanggal', '=', $dateSearch)
                    ->orWhere('no_bukti', 'like', "%{$search}%")
                    ->orWhere('kategori', 'like', "%{$search}%")
                    ->orWhere('keterangan', 'like', "%{$search}%");
            });
        }

        if ($request->header('Accept') === 'application/json') {
            $totalQuery = clone $query;
            $draftQuery = clone $query;
            $paginated = $query->paginate($perPage);
            $totals = $totalQuery->reorder()->selectRaw('
                SUM(total_rs) as grand_rs,
                SUM(total_pelayanan) as grand_pelayanan,
                SUM(total_all) as grand_total
            ')->first();

            $draftCount = (clone $draftQuery)->where('is_posted', false)->count();
            $postedCount = (clone $draftQuery)->where('is_posted', true)->count();

            return response()->json([
                'data' => $paginated->items(),
                'from' => $paginated->firstItem(),
                'to' => $paginated->lastItem(),
                'total' => $paginated->total(),
                'total_draft' => $draftCount,
                'total_posted' => $postedCount,
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'aggregates' => [
                    'total_rs' => $totals->grand_rs ?? 0,
                    'total_pelayanan' => $totals->grand_pelayanan ?? 0,
                    'total_all' => $totals->grand_total ?? 0,
                ]
            ]);
        }

        return $query->paginate($perPage);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tanggal' => 'required|date',
            'tanggal_rk' => 'nullable|date',
            'kategori' => 'required|in:UMUM,BPJS,JAMINAN,LAIN,KERJASAMA',
            'no_bukti' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string|max:500',
        ]);

        $permMap = [
            'UMUM' => 'PENDAPATAN_UMUM_CREATE',
            'BPJS' => 'PENDAPATAN_BPJS_CREATE',
            'JAMINAN' => 'PENDAPATAN_JAMINAN_CREATE',
            'LAIN' => 'PENDAPATAN_LAIN_CREATE',
            'KERJASAMA' => 'PENDAPATAN_KERJA_CREATE',
        ];
        abort_unless(auth()->user()->hasPermission($permMap[$data['kategori']]) || auth()->user()->hasPermission('MASTER_CREATE'), 403);

        $data['tahun'] = session('tahun_anggaran');

        $master = RevenueMaster::create($data);

        ActivityLog::log(
            'CREATE',
            'REVENUE_MASTER',
            "Menambah kelompok pendapatan {$master->kategori} pada tanggal " . \Carbon\Carbon::parse($master->tanggal)->format('d/m/Y'),
            $master->id,
            null,
            $master->toArray()
        );

        self::recalculate($master->id);

        return response()->json(['success' => true, 'data' => $master]);
    }

    public function update(Request $request, $id)
    {
        $master = RevenueMaster::findOrFail($id);

        $permMap = [
            'UMUM' => 'PENDAPATAN_UMUM_CREATE',
            'BPJS' => 'PENDAPATAN_BPJS_CREATE',
            'JAMINAN' => 'PENDAPATAN_JAMINAN_CREATE',
            'LAIN' => 'PENDAPATAN_LAIN_CREATE',
            'KERJASAMA' => 'PENDAPATAN_KERJA_CREATE',
        ];
        abort_unless(auth()->user()->hasPermission($permMap[$master->kategori]) || auth()->user()->hasPermission('MASTER_CREATE'), 403);
        abort_if($master->is_posted, 403, 'Kelompok yang sudah diposting tidak dapat diubah.');

        $data = $request->validate([
            'tanggal' => 'required|date',
            'tanggal_rk' => 'nullable|date',
            'kategori' => 'required|in:UMUM,BPJS,JAMINAN,LAIN,KERJASAMA',
            'no_bukti' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string|max:500',
        ]);

        $oldValues = $master->toArray();
        $master->update($data);

        ActivityLog::log(
            'UPDATE',
            'REVENUE_MASTER',
            "Mengubah kelompok pendapatan {$master->kategori} pada tanggal " . \Carbon\Carbon::parse($master->tanggal)->format('d/m/Y'),
            $master->id,
            $oldValues,
            $master->toArray()
        );

        self::recalculate($master->id);

        return response()->json(['success' => true, 'data' => $master]);
    }

    public function show($id)
    {
        $master = RevenueMaster::findOrFail($id);
        return response()->json($master);
    }

    public function destroy($id)
    {
        $master = RevenueMaster::findOrFail($id);

        $permMap = [
            'UMUM' => 'PENDAPATAN_UMUM_DELETE',
            'BPJS' => 'PENDAPATAN_BPJS_DELETE',
            'JAMINAN' => 'PENDAPATAN_JAMINAN_DELETE',
            'LAIN' => 'PENDAPATAN_LAIN_DELETE',
            'KERJASAMA' => 'PENDAPATAN_KERJA_DELETE',
        ];
        abort_unless(auth()->user()->hasPermission($permMap[$master->kategori]) || auth()->user()->hasPermission('MASTER_DELETE'), 403);
        abort_if($master->is_posted, 403, 'Kelompok yang sudah diposting tidak dapat dihapus.');

        $hasDetail = false;
        if ($master->kategori === 'UMUM') {
            $hasDetail = $master->pendapatanUmums()->exists();
        } elseif ($master->kategori === 'BPJS') {
            $hasDetail = $master->pendapatanBpjs()->exists();
        } elseif ($master->kategori === 'JAMINAN') {
            $hasDetail = $master->pendapatanJaminans()->exists();
        } elseif ($master->kategori === 'LAIN') {
            $hasDetail = $master->pendapatanLains()->exists();
        } elseif ($master->kategori === 'KERJASAMA') {
            $hasDetail = $master->pendapatanKerjasamas()->exists();
        }

        if ($hasDetail) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menghapus kelompok karena masih memiliki rincian pasien di dalamnya. Hapus rincian terlebih dahulu.'
            ], 422);
        }

        $oldValues = $master->toArray();
        $master->delete();

        ActivityLog::log(
            'DELETE',
            'REVENUE_MASTER',
            "Menghapus kelompok pendapatan {$master->kategori} pada tanggal " . \Carbon\Carbon::parse($master->tanggal)->format('d/m/Y'),
            $id,
            $oldValues,
            null
        );

        return response()->json(['success' => true]);
    }

    public function togglePost($id)
    {
        $master = RevenueMaster::findOrFail($id);

        $permMap = [
            'UMUM' => 'PENDAPATAN_UMUM_POST',
            'BPJS' => 'PENDAPATAN_BPJS_POST',
            'JAMINAN' => 'PENDAPATAN_JAMINAN_POST',
            'LAIN' => 'PENDAPATAN_LAIN_POST',
            'KERJASAMA' => 'PENDAPATAN_KERJA_POST',
        ];

        abort_unless(auth()->user()->hasPermission($permMap[$master->kategori]) || auth()->user()->hasPermission('PENGESAHAN_POST'), 403);

        if (!$master->is_posted && empty($master->tanggal_rk)) {
            return response()->json(['message' => 'Tanggal Rekening Koran (RK) belum diisi pada kelompok ini. Silakan Edit master kelompok dan isi Tanggal RK terlebih dahulu sebelum memposting.'], 422);
        }

        $master->is_posted = !$master->is_posted;
        $master->save();

        self::recalculate($master->id);

        $statusStr = $master->is_posted ? 'DIPOSTING' : 'BATAL POSTING';
        ActivityLog::log(
            'UPDATE',
            'REVENUE_MASTER',
            "{$statusStr} kelompok pendapatan {$master->kategori} pada tanggal " . \Carbon\Carbon::parse($master->tanggal)->format('d/m/Y'),
            $master->id,
            null,
            $master->toArray()
        );

        return response()->json(['success' => true, 'data' => $master]);
    }

    public function bulkPost(Request $request)
    {
        $request->validate([
            'ids' => 'nullable|array',
            'all_pages' => 'nullable|boolean',
            'kategori' => 'required_if:all_pages,true|string',
            'search' => 'nullable|string'
        ]);

        $query = RevenueMaster::where('is_posted', false)->where('tahun', session('tahun_anggaran'));

        if ($request->all_pages) {
            $query->where('kategori', $request->kategori);
            if ($request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('keterangan', 'like', "%{$search}%")
                        ->orWhere('no_bukti', 'like', "%{$search}%")
                        ->orWhereDate('tanggal', '=', $search);
                });
            }
            $ids = $query->pluck('id')->toArray();
        } else {
            $ids = $request->ids ?? [];
        }

        $count = 0;
        $failedCount = 0;
        $messages = [];

        foreach ($ids as $id) {
            $master = RevenueMaster::find($id);
            if (!$master)
                continue;

            $permMap = [
                'UMUM' => 'PENDAPATAN_UMUM_POST',
                'BPJS' => 'PENDAPATAN_BPJS_POST',
                'JAMINAN' => 'PENDAPATAN_JAMINAN_POST',
                'LAIN' => 'PENDAPATAN_LAIN_POST',
                'KERJASAMA' => 'PENDAPATAN_KERJA_POST',
            ];

            if (!auth()->user()->hasPermission($permMap[$master->kategori]) && !auth()->user()->hasPermission('PENGESAHAN_POST'))
                continue;

            if (!$master->is_posted) {
                if (empty($master->tanggal_rk)) {
                    $failedCount++;
                    $messages[] = "Kelompok " . \Carbon\Carbon::parse($master->tanggal)->format('d/m/Y') . " gagal diposting: Tanggal RK kosong.";
                    continue;
                }

                $master->is_posted = true;
                $master->save();
                self::recalculate($master->id);
                $count++;
            }
        }

        ActivityLog::log('UPDATE', 'REVENUE_MASTER', "Posting massal {$count} kelompok pendapatan", null, null, null);

        return response()->json([
            'success' => true,
            'posted_count' => $count,
            'failed_count' => $failedCount,
            'messages' => $messages
        ]);
    }

    public function bulkUnpost(Request $request)
    {
        $request->validate([
            'ids' => 'nullable|array',
            'all_pages' => 'nullable|boolean',
            'kategori' => 'required_if:all_pages,true|string',
            'search' => 'nullable|string'
        ]);

        $query = RevenueMaster::where('is_posted', true)->where('tahun', session('tahun_anggaran'));

        if ($request->all_pages) {
            $query->where('kategori', $request->kategori);
            if ($request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('keterangan', 'like', "%{$search}%")
                        ->orWhere('no_bukti', 'like', "%{$search}%")
                        ->orWhereDate('tanggal', '=', $search);
                });
            }
            $ids = $query->pluck('id')->toArray();
        } else {
            $ids = $request->ids ?? [];
        }

        $count = 0;
        foreach ($ids as $id) {
            $master = RevenueMaster::find($id);
            if (!$master)
                continue;

            $permMap = [
                'UMUM' => 'PENDAPATAN_UMUM_POST',
                'BPJS' => 'PENDAPATAN_BPJS_POST',
                'JAMINAN' => 'PENDAPATAN_JAMINAN_POST',
                'LAIN' => 'PENDAPATAN_LAIN_POST',
                'KERJASAMA' => 'PENDAPATAN_KERJA_POST',
            ];

            if (!auth()->user()->hasPermission($permMap[$master->kategori]) && !auth()->user()->hasPermission('PENGESAHAN_POST'))
                continue;

            if ($master->is_posted) {
                $master->is_posted = false;
                $master->save();
                self::recalculate($master->id);
                $count++;
            }
        }

        ActivityLog::log('UPDATE', 'REVENUE_MASTER', "Batal Posting massal {$count} kelompok pendapatan", null, null, null);

        return response()->json([
            'success' => true,
            'unposted_count' => $count
        ]);
    }

    /**
     * Recalculates the aggregates for a given master record
     */
    public static function recalculate($id)
    {
        $master = RevenueMaster::find($id);
        if (!$master)
            return;

        $rs = 0;
        $pelayanan = 0;
        $all = 0;

        if ($master->kategori === 'UMUM') {
            $rs = $master->pendapatanUmums()->sum(\DB::raw('rs_tindakan + rs_obat'));
            $pelayanan = $master->pendapatanUmums()->sum(\DB::raw('pelayanan_tindakan + pelayanan_obat'));
            $all = $master->pendapatanUmums()->sum('total');
        } elseif ($master->kategori === 'BPJS') {
            $rs = $master->pendapatanBpjs()->sum(\DB::raw('rs_tindakan + rs_obat'));
            $pelayanan = $master->pendapatanBpjs()->sum(\DB::raw('pelayanan_tindakan + pelayanan_obat'));
            $all = $master->pendapatanBpjs()->sum('total');
        } elseif ($master->kategori === 'JAMINAN') {
            $rs = $master->pendapatanJaminans()->sum(\DB::raw('rs_tindakan + rs_obat'));
            $pelayanan = $master->pendapatanJaminans()->sum(\DB::raw('pelayanan_tindakan + pelayanan_obat'));
            $all = $master->pendapatanJaminans()->sum('total');
        } elseif ($master->kategori === 'LAIN') {
            $rs = $master->pendapatanLains()->sum(\DB::raw('rs_tindakan + rs_obat'));
            $pelayanan = $master->pendapatanLains()->sum(\DB::raw('pelayanan_tindakan + pelayanan_obat'));
            $all = $master->pendapatanLains()->sum('total');
        } elseif ($master->kategori === 'KERJASAMA') {
            $rs = $master->pendapatanKerjasamas()->sum(\DB::raw('rs_tindakan + rs_obat'));
            $pelayanan = $master->pendapatanKerjasamas()->sum(\DB::raw('pelayanan_tindakan + pelayanan_obat'));
            $all = $master->pendapatanKerjasamas()->sum('total');
        }

        $master->update([
            'total_rs' => $rs,
            'total_pelayanan' => $pelayanan,
            'total_all' => $all,
        ]);

        \App\Models\RekeningKoran::where('revenue_master_id', $master->id)->delete();
        if ($master->is_posted && $master->tanggal_rk && $all > 0) {
            $details = match ($master->kategori) {
                'UMUM' => $master->pendapatanUmums,
                'BPJS' => $master->pendapatanBpjs,
                'JAMINAN' => $master->pendapatanJaminans,
                'LAIN' => $master->pendapatanLains,
                'KERJASAMA' => $master->pendapatanKerjasamas,
                default => collect()
            };

            $tunaiBucket = [
                'Bank Riau Kepri Syariah' => 0,
                'Bank Syariah Indonesia' => 0,
            ];

            foreach ($details as $detail) {
                if ($detail->total <= 0)
                    continue;

                $bankName = ($detail->bank === 'BSI' || $detail->bank === 'Bank Syariah Indonesia')
                    ? 'Bank Syariah Indonesia'
                    : 'Bank Riau Kepri Syariah';

                if ($detail->metode_pembayaran === 'TUNAI') {
                    $tunaiBucket[$bankName] += (float) $detail->total;
                } else {
                    // NON-TUNAI: List individually with patient name
                    \App\Models\RekeningKoran::create([
                        'revenue_master_id' => $master->id,
                        'tanggal' => $master->tanggal_rk,
                        'tahun' => $master->tahun,
                        'bank' => $bankName,
                        'keterangan' => ($master->keterangan ?: "Penerimaan {$master->kategori}") . " - ({$detail->nama_pasien})",
                        'cd' => 'C',
                        'jumlah' => $detail->total,
                    ]);
                }
            }

            // Insert Grouped Tunai per bank
            foreach ($tunaiBucket as $bank => $total) {
                if ($total > 0) {
                    \App\Models\RekeningKoran::create([
                        'revenue_master_id' => $master->id,
                        'tanggal' => $master->tanggal_rk,
                        'tahun' => $master->tahun,
                        'bank' => $bank,
                        'keterangan' => ($master->keterangan ?: "Penerimaan {$master->kategori}") . " - TUNAI",
                        'cd' => 'C',
                        'jumlah' => $total,
                    ]);
                }
            }
        }

        return $master;
    }
}
