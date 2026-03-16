<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SPTJBController extends Controller
{
    public function index()
    {
        $this->authorizePermission('SPTJB_VIEW');
        return response()->json([]);
    }

    public function show($id)
    {
        $this->authorizePermission('SPTJB_VIEW');
        return response()->json(['id' => $id]);
    }

    public function generate(Request $request)
    {
        $this->authorizePermission('SPTJB_GENERATE');
        return response()->json(['message' => 'SPTJB generated successfully']);
    }

    public function validateSptjb($id)
    {
        $this->authorizePermission('SPTJB_GENERATE'); // Validation is part of Generate/Management
        return response()->json(['message' => 'SPTJB validated successfully']);
    }

    public function print($id)
    {
        $this->authorizePermission('SPTJB_PRINT');
        return response()->json(['message' => 'Printing SPTJB...']);
    }

    public function destroy($id)
    {
        $this->authorizePermission('SPTJB_GENERATE');
        return response()->json(['message' => 'SPTJB deleted successfully']);
    }
}





