<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expenditure;
use App\Services\ExpenditureService;
use Illuminate\Support\Facades\DB;

class ExpenditureController extends Controller
{
    protected $service;

    public function __construct(ExpenditureService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        // Permission check (using same names as before or mapping)
        abort_unless(auth()->user()->hasPermission('BELANJA_VIEW') || auth()->user()->hasPermission('SP2D_MANAGE') || auth()->user()->hasPermission('SPP_MANAGE') || auth()->user()->isAdmin(), 403);

        $kategori = $request->get('kategori'); // PEGAWAI, BARANG_JASA, MODAL
        $type = $request->get('spending_type'); // UP, LS
        $search = $request->get('search');
        $limit = $request->get('limit', 10);

        $query = Expenditure::with(['kodeRekening']);

        if ($request->has('fund_disbursement_id')) {
            $query->where('fund_disbursement_id', $request->get('fund_disbursement_id'));
        }

        if ($kategori) {
            $query->whereHas('kodeRekening', function ($q) use ($kategori) {
                $q->where('sumber_data', $kategori);
            });
        }

        if ($type) {
            $query->where('spending_type', $type);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%$search%")
                    ->orWhere('vendor', 'like', "%$search%")
                    ->orWhere('no_bukti', 'like', "%$search%")
                    ->orWhereHas('kodeRekening', function ($qr) use ($search) {
                        $qr->where('nama', 'like', "%$search%")
                            ->orWhere('kode', 'like', "%$search%");
                    });
            });
        }

        // Aggregates
        $totalGross = (float) (clone $query)->sum('gross_value');
        $totalTax = (float) (clone $query)->sum('tax');
        $totalNet = (float) (clone $query)->sum('net_value');

        // Detailed aggregated for cards
        $upStats = (clone $query)->where('spending_type', 'UP')->selectRaw('SUM(gross_value) as total, COUNT(*) as count')->first();
        $guStats = (clone $query)->where('spending_type', 'GU')->selectRaw('SUM(gross_value) as total, COUNT(*) as count')->first();
        $lsStats = (clone $query)->where('spending_type', 'LS')->selectRaw('SUM(gross_value) as total, COUNT(*) as count')->first();

        // Dynamic Sorting
        $sortBy = $request->get('sort_by', 'spending_date');
        $sortDir = $request->get('sort_dir', 'desc');

        $allowedSortColumns = ['spending_date', 'spending_type', 'description', 'gross_value', 'id', 'no_bukti'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'spending_date';
        }
        $sortDir = strtolower($sortDir) === 'asc' ? 'asc' : 'desc';

        $data = $query->orderBy($sortBy, $sortDir)->orderBy('id', 'desc')->paginate($limit);

        $response = $data->toArray();
        $response['aggregates'] = [
            'total_gross' => $totalGross,
            'total_tax' => $totalTax,
            'total_net' => $totalNet,
            'total_count' => $data->total(),
            'up' => ['total' => (float) ($upStats->total ?? 0), 'count' => (int) ($upStats->count ?? 0)],
            'gu' => ['total' => (float) ($guStats->total ?? 0), 'count' => (int) ($guStats->count ?? 0)],
            'ls' => ['total' => (float) ($lsStats->total ?? 0), 'count' => (int) ($lsStats->count ?? 0)],
        ];

        return response()->json($response);
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('BELANJA_MANAGE') || auth()->user()->hasPermission('SP2D_MANAGE') || auth()->user()->hasPermission('SPP_MANAGE') || auth()->user()->isAdmin(), 403);

        $data = $request->validate([
            'spending_date' => 'required|date',
            'kode_rekening_id' => 'required|exists:kode_rekening,id',
            'description' => 'required|string|max:255',
            'gross_value' => 'required|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'spending_type' => 'required|in:UP,GU,LS',
            'siklus_up' => 'nullable|integer',
            'vendor' => 'nullable|string|max:255',
            'fund_disbursement_id' => 'nullable|exists:fund_disbursements,id',
            'no_bukti' => 'required|string|max:255',
        ]);

        try {
            // No. Bukti uniqueness check
            $exists = Expenditure::where('no_bukti', $data['no_bukti'])->exists();
            if ($exists) {
                return response()->json([
                    'message' => 'Nomor bukti sudah digunakan pada kegiatan lain.',
                    'errors' => ['no_bukti' => ['Nomor bukti sudah digunakan pada kegiatan lain.']]
                ], 422);
            }

            // Budget Check
            $budgetCheck = $this->service->checkBudget($data['kode_rekening_id'], $data['spending_date'], $data['gross_value']);
            if (!$budgetCheck['isValid']) {
                return response()->json([
                    'message' => $budgetCheck['message'],
                    'errors' => ['gross_value' => [$budgetCheck['message']]]
                ], 422);
            }

            $expenditure = $this->service->store($data);
            return response()->json($expenditure, 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => ['gross_value' => [$e->getMessage()]]
            ], 422);
        }
    }

    public function show($id)
    {
        abort_unless(auth()->user()->hasPermission('BELANJA_VIEW') || auth()->user()->hasPermission('SP2D_MANAGE') || auth()->user()->hasPermission('SPP_MANAGE') || auth()->user()->isAdmin(), 403);
        $expenditure = Expenditure::with('kodeRekening')->findOrFail($id);
        return response()->json($expenditure);
    }

    public function update(Request $request, $id)
    {
        abort_unless(auth()->user()->hasPermission('BELANJA_MANAGE') || auth()->user()->hasPermission('SP2D_MANAGE') || auth()->user()->hasPermission('SPP_MANAGE') || auth()->user()->isAdmin(), 403);

        $data = $request->validate([
            'spending_date' => 'required|date',
            'kode_rekening_id' => 'required|exists:kode_rekening,id',
            'description' => 'required|string|max:255',
            'gross_value' => 'required|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'spending_type' => 'required|in:UP,GU,LS',
            'siklus_up' => 'nullable|integer',
            'vendor' => 'nullable|string|max:255',
            'fund_disbursement_id' => 'nullable|exists:fund_disbursements,id',
            'no_bukti' => 'required|string|max:255',
        ]);

        try {
            // No. Bukti uniqueness check (exclude current record)
            $exists = Expenditure::where('no_bukti', $data['no_bukti'])
                ->where('id', '!=', $id)
                ->exists();
            if ($exists) {
                return response()->json([
                    'message' => 'Nomor bukti sudah digunakan pada kegiatan lain.',
                    'errors' => ['no_bukti' => ['Nomor bukti sudah digunakan pada kegiatan lain.']]
                ], 422);
            }

            $budgetCheck = $this->service->checkBudget($data['kode_rekening_id'], $data['spending_date'], $data['gross_value'], $id);
            if (!$budgetCheck['isValid']) {
                return response()->json([
                    'message' => $budgetCheck['message'],
                    'errors' => ['gross_value' => [$budgetCheck['message']]]
                ], 422);
            }

            $expenditure = $this->service->update($id, $data);
            return response()->json($expenditure);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => ['gross_value' => [$e->getMessage()]]
            ], 422);
        }
    }

    public function checkNoBukti(Request $request)
    {
        $noBukti = $request->get('no_bukti', '');
        $excludeId = $request->get('exclude_id');

        if (empty($noBukti)) {
            return response()->json(['available' => true]);
        }

        $query = Expenditure::where('no_bukti', $noBukti);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return response()->json(['available' => !$query->exists()]);
    }

    public function destroy($id)
    {
        abort_unless(auth()->user()->hasPermission('BELANJA_MANAGE') || auth()->user()->hasPermission('SP2D_MANAGE') || auth()->user()->hasPermission('SPP_MANAGE') || auth()->user()->isAdmin(), 403);
        try {
            $this->service->delete($id);
            return response()->json(['status' => 'ok']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}





