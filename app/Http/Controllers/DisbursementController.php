<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FundDisbursement;
use App\Services\DisbursementService;

class DisbursementController extends Controller
{
    protected $service;

    public function __construct(DisbursementService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('PENGELUARAN_CAIR') || auth()->user()->isAdmin(), 403);

        $type = $request->get('type');
        $status = $request->get('status');
        $search = $request->get('search');
        $limit = $request->get('limit', 10);

        $query = FundDisbursement::with('kodeRekening');

        if ($request->has('id')) {
            $query->where('id', $request->get('id'));
        }

        if ($type) {
            $types = explode(',', $type);
            $query->whereIn('type', $types);
        }

        if ($status) {
            $statuses = explode(',', $status);
            $query->whereIn('status', $statuses);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('sp2d_no', 'like', "%$search%")
                    ->orWhere('spm_no', 'like', "%$search%")
                    ->orWhere('spp_no', 'like', "%$search%")
                    ->orWhere('recipient_party', 'like', "%$search%");
            });
        }

        if ($request->has('is_saldo')) {
            $isSaldo = filter_var($request->get('is_saldo'), FILTER_VALIDATE_BOOLEAN);
            if ($isSaldo) {
                // Saldo includes legacy manual entries (null spp_no) 
                // AND workflow-based refills (null kode_rekening_id)
                $query->where('status', 'CAIR')->where(function ($q) {
                    $q->whereNull('spp_no')->orWhereNull('kode_rekening_id');
                });
            } else {
                $query->where(function ($q) {
                    $q->whereNotNull('spp_no')->orWhere('status', '!=', 'CAIR');
                });
            }
        }

        return response()->json($query->with(['expenditures.kodeRekening', 'kodeRekening'])->orderBy('sp2d_date', 'asc')->orderBy('id', 'asc')->paginate($limit));
    }

    public function getNextSiklus(Request $request)
    {
        $type = $request->get('type', 'GU');
        $year = $request->get('year', date('Y'));

        $max = FundDisbursement::where('tahun', $year)
            ->where('type', $type)
            ->max('siklus_up') ?? 0;

        return response()->json(['next' => $max + 1]);
    }

    public function availableSiklus(Request $request)
    {
        $year = $request->get('year', date('Y'));
        $type = $request->get('type', 'GU');

        $list = FundDisbursement::where('tahun', $year)
            ->where('type', $type)
            ->whereNotNull('siklus_up')
            ->select('siklus_up')
            ->distinct()
            ->orderBy('siklus_up', 'asc')
            ->get();

        return response()->json($list);
    }

    public function getSisaAnggaran(Request $request)
    {
        $kodeRekeningId = $request->get('kode_rekening_id');
        $year = $request->get('year', date('Y'));

        if (!$kodeRekeningId) {
            return response()->json(['anggaran' => 0, 'realisasi' => 0, 'sisa' => 0]);
        }

        $anggaran = (float) \App\Models\AnggaranRekening::where('tahun', $year)
            ->where('kode_rekening_id', $kodeRekeningId)
            ->sum('nilai');

        $realisasi = (float) \App\Models\Expenditure::whereYear('spending_date', $year)
            ->where('kode_rekening_id', $kodeRekeningId)
            ->sum('gross_value');

        // LS Cairns are also realizations
        $lsCairQuery = FundDisbursement::where('tahun', $year)
            ->where('kode_rekening_id', $kodeRekeningId)
            ->where('status', 'CAIR');

        if ($request->has('exclude_id')) {
            $lsCairQuery->where('id', '!=', $request->get('exclude_id'));
        }
        $lsCair = (float) $lsCairQuery->sum('value');

        $sppPendingQuery = FundDisbursement::where('tahun', $year)
            ->where('kode_rekening_id', $kodeRekeningId)
            ->whereIn('status', ['SPP', 'SPM']);

        if ($request->has('exclude_id')) {
            $sppPendingQuery->where('id', '!=', $request->get('exclude_id'));
        }

        $sppPending = (float) $sppPendingQuery->sum('value');

        $totalUsed = $realisasi + $lsCair + $sppPending;
        $sisa = $anggaran - $totalUsed;

        return response()->json([
            'anggaran' => $anggaran,
            'realisasi' => $realisasi,
            'spp_pending' => $sppPending,
            'sisa' => $sisa,
        ]);
    }

    public function getSaldoKas(Request $request)
    {
        $year = $request->get('year', date('Y'));
        $type = $request->get('type', 'UP'); // UP, GU, or LS
        $siklus = $request->get('siklus_up');

        $qCair = FundDisbursement::where('tahun', $year)
            ->where('type', $type)
            ->where('status', 'CAIR');

        $qBelanja = \App\Models\Expenditure::whereYear('spending_date', $year)
            ->where('spending_type', $type);

        $qPending = FundDisbursement::where('tahun', $year)
            ->where('type', $type)
            ->whereIn('status', ['SPP', 'SPM']);

        if ($request->has('exclude_id')) {
            $qPending->where('id', '!=', $request->get('exclude_id'));
        }

        if ($type === 'GU' && $siklus) {
            $qCair->where('siklus_up', $siklus);
            $qBelanja->where('siklus_up', $siklus);
            $qPending->where('siklus_up', $siklus);
        }

        // Total dana yang sudah CAIR untuk tipe ini (Dana Awal / Masuk)
        // Includes legacy (null spp_no) or workflow refills (null kode_rekening_id)
        $totalCair = (float) (clone $qCair)->where(function ($q) {
            $q->whereNull('spp_no')->orWhereNull('kode_rekening_id');
        })->sum('value');

        // Total belanja yang menggunakan metode ini (Expenditure Lapor BKU + SPP Keluar)
        // SPP Keluar is disbursement WITH an activity (kode_rekening_id or expenditure_id)
        $sppKeluar = (float) (clone $qCair)->where(function ($q) {
            $q->whereNotNull('kode_rekening_id')->orWhereNotNull('expenditure_id');
        })->sum('value');
        $totalBelanja = (float) $qBelanja->sum('gross_value') + $sppKeluar;

        // SPP/SPM yang masih dalam proses (belum cair) - Hanya yang bersifat pengeluaran (ada rekening/uraian kegiatan)
        $sppPending = (float) $qPending->where(function ($q) {
            $q->whereNotNull('kode_rekening_id')->orWhereNotNull('expenditure_id');
        })->sum('value');

        $sisaKas = $totalCair - $totalBelanja - $sppPending;

        $label = $type;
        if ($type === 'GU' && $siklus) {
            $label = "GU-{$siklus}";
        }

        return response()->json([
            'label' => $label,
            'type' => $type,
            'siklus' => $siklus,
            'total_cair' => $totalCair,
            'total_belanja' => $totalBelanja,
            'spp_pending' => $sppPending,
            'sisa_kas' => $sisaKas,
        ]);
    }

    public function getSaldoSummary(Request $request)
    {
        $year = $request->get('year', date('Y'));

        $result = [];

        // UP — satu kartu, tanpa siklus
        $upCair = (float) FundDisbursement::where('tahun', $year)->where('type', 'UP')->where('status', 'CAIR')->where(function ($q) {
            $q->whereNull('spp_no')->orWhereNull('kode_rekening_id');
        })->sum('value');
        $upSppCair = (float) FundDisbursement::where('tahun', $year)->where('type', 'UP')->whereIn('status', ['SPP', 'SPM', 'CAIR'])->where(function ($q) {
            $q->whereNotNull('kode_rekening_id')->orWhereNotNull('expenditure_id');
        })->sum('value');
        $upBelanja = (float) \App\Models\Expenditure::whereYear('spending_date', $year)->where('spending_type', 'UP')->sum('gross_value') + $upSppCair;
        $upPending = (float) FundDisbursement::where('tahun', $year)->where('type', 'UP')->whereIn('status', ['SPP', 'SPM'])->where(function ($q) {
            $q->whereNotNull('kode_rekening_id')->orWhereNotNull('expenditure_id');
        })->sum('value');
        $result[] = [
            'label' => 'UP',
            'type' => 'UP',
            'siklus' => null,
            'total_cair' => $upCair,
            'total_belanja' => $upBelanja,
            'spp_pending' => $upPending,
            'sisa_kas' => $upCair - $upBelanja,
        ];

        // GU — per siklus (GU-1, GU-2, dst)
        $guSiklus = FundDisbursement::where('tahun', $year)
            ->where('type', 'GU')
            ->whereNotNull('siklus_up')
            ->select('siklus_up')
            ->distinct()
            ->orderBy('siklus_up')
            ->pluck('siklus_up');

        if ($guSiklus->isEmpty()) {
            // Default GU-1 card
            $result[] = [
                'label' => 'GU-1',
                'type' => 'GU',
                'siklus' => 1,
                'total_cair' => 0,
                'total_belanja' => 0,
                'spp_pending' => 0,
                'sisa_kas' => 0,
            ];
        } else {
            foreach ($guSiklus as $siklus) {
                // Including SPP, SPM, CAIR as "Liquid" in the treasurer's perspective for these cards
                $cair = (float) FundDisbursement::where('tahun', $year)->where('type', 'GU')->where('siklus_up', $siklus)->where('status', 'CAIR')->where(function ($q) {
                    $q->whereNull('spp_no')->orWhereNull('kode_rekening_id');
                })->sum('value');
                $sppCair = (float) FundDisbursement::where('tahun', $year)->where('type', 'GU')->where('siklus_up', $siklus)->whereIn('status', ['SPP', 'SPM', 'CAIR'])->where(function ($q) {
                    $q->whereNotNull('kode_rekening_id')->orWhereNotNull('expenditure_id');
                })->sum('value');
                $belanja = (float) \App\Models\Expenditure::whereYear('spending_date', $year)->where('spending_type', 'GU')->where('siklus_up', $siklus)->sum('gross_value') + $sppCair;
                $pending = (float) FundDisbursement::where('tahun', $year)->where('type', 'GU')->where('siklus_up', $siklus)->whereIn('status', ['SPP', 'SPM'])->where(function ($q) {
                    $q->whereNotNull('kode_rekening_id')->orWhereNotNull('expenditure_id');
                })->sum('value');

                $result[] = [
                    'label' => "GU-{$siklus}",
                    'type' => 'GU',
                    'siklus' => $siklus,
                    'total_cair' => $cair,
                    'total_belanja' => $belanja,
                    'spp_pending' => $pending,
                    'sisa_kas' => $cair - $belanja,
                ];
            }
        }


        return response()->json($result);
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('PENGELUARAN_CAIR') || auth()->user()->isAdmin(), 403);

        $data = $request->validate([
            'type' => 'required|in:UP,GU,LS',
            'sp2d_date' => 'required|date',
            'value' => 'required|numeric|min:0',
            'recipient_party' => 'nullable|string|max:255',
            'destination_account' => 'nullable|string|max:255',
            'spj_id' => 'nullable|exists:spj,id',
            'expenditure_id' => 'nullable|exists:expenditures,id',
            'kode_rekening_id' => 'nullable|exists:kode_rekening,id',
            'uraian' => 'nullable|string|max:500',
            'status' => 'nullable|in:DRAFT,SPP,SPM,CAIR',
            'siklus_up' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
        ]);

        $disbursement = $this->service->store($data);

        return response()->json($disbursement, 201);
    }

    public function show($id)
    {
        abort_unless(auth()->user()->hasPermission('PENGELUARAN_CAIR') || auth()->user()->isAdmin(), 403);
        return response()->json(FundDisbursement::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        abort_unless(auth()->user()->hasPermission('PENGELUARAN_CAIR') || auth()->user()->isAdmin(), 403);

        $disbursement = FundDisbursement::findOrFail($id);
        $data = $request->all();

        $disbursement->update($data);
        return response()->json($disbursement);
    }

    public function updateStatus(Request $request, $id)
    {
        abort_unless(auth()->user()->hasPermission('PENGELUARAN_CAIR') || auth()->user()->isAdmin(), 403);

        $data = $request->validate([
            'status' => 'required|in:DRAFT,SPP,SPM,CAIR',
            'spp_no' => 'nullable|string',
            'spm_no' => 'nullable|string',
            'sp2d_no' => 'nullable|string',
        ]);

        try {
            $disbursement = $this->service->updateStatus($id, $data['status'], $data);
            return response()->json($disbursement);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function destroy($id)
    {
        abort_unless(auth()->user()->hasPermission('PENGELUARAN_CAIR') || auth()->user()->isAdmin(), 403);
        try {
            $this->service->destroy($id);
            return response()->json(['status' => 'ok']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function revertStatus(Request $request, $id)
    {
        abort_unless(auth()->user()->hasPermission('PENGELUARAN_CAIR') || auth()->user()->isAdmin(), 403);

        $data = $request->validate([
            'target_status' => 'required|in:SPP,SPM'
        ]);

        try {
            $disbursement = $this->service->revertStatus($id, $data['target_status']);
            return response()->json($disbursement);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
