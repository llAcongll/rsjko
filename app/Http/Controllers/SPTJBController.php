<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SPTJBController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->hasPermission('SPTJB_VIEW'), 403);
        return response()->json([]);
    }

    public function show($id)
    {
        abort_unless(auth()->user()->hasPermission('SPTJB_VIEW'), 403);
        return response()->json(['id' => $id]);
    }

    public function generate(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('SPTJB_GENERATE'), 403);
        return response()->json(['message' => 'SPTJB generated successfully']);
    }

    public function validateSptjb($id)
    {
        abort_unless(auth()->user()->hasPermission('SPTJB_GENERATE'), 403); // Validation is part of Generate/Management
        return response()->json(['message' => 'SPTJB validated successfully']);
    }

    public function print($id)
    {
        abort_unless(auth()->user()->hasPermission('SPTJB_PRINT'), 403);
        return response()->json(['message' => 'Printing SPTJB...']);
    }

    public function destroy($id)
    {
        abort_unless(auth()->user()->hasPermission('SPTJB_GENERATE'), 403);
        return response()->json(['message' => 'SPTJB deleted successfully']);
    }
}





