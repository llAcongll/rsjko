<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SafeSpaceScreening;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SafeSpaceApiController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validasi
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|string',
            'school_id' => 'nullable|exists:schools,id',
            'started_at' => 'nullable|date',
            'completed_at' => 'nullable|date',
            'score' => 'nullable|integer',
            'anxiety_result' => 'nullable|string|in:none,mild,moderate,severe',
            'depression_result' => 'nullable|string|in:none,mild,moderate,severe',
            'safety_answer' => 'nullable|string|in:yes,no',
            'safety_status' => 'nullable|string|in:safe,unsafe',
            'follow_up' => 'nullable|string|in:tips,curhat,rsjko,bk_puskesmas,none',
        ]);

        if ($validator->fails()) {
            Log::warning('Safe Space API Validation Failed', [
                'errors' => $validator->errors(),
                'session_id' => $request->input('session_id')
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        try {
            // Update or Create based on session_id to prevent duplicates on retry
            $screening = SafeSpaceScreening::updateOrCreate(
                ['session_id' => $data['session_id']],
                $data
            );

            Log::info('Safe Space API: Screening saved', ['session_id' => $screening->session_id]);

            return response()->json([
                'success' => true,
                'message' => 'Screening data saved successfully',
                'data' => [
                    'id' => $screening->id,
                    'session_id' => $screening->session_id
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Safe Space API Error: ' . $e->getMessage(), [
                'session_id' => $request->input('session_id')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }
}
