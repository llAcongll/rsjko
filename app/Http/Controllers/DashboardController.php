<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller as BaseController;

class DashboardController extends BaseController
{
    public function content(string $page, ?string $param = null)
    {
        return match ($page) {

            'dashboard' => view('dashboard.pages.dashboard'),

            'users' => Auth::user()->isAdmin()
                ? view('dashboard.pages.users', [
                    'users' => User::orderBy('username')->get()
                  ])
                : abort(403),

            'pendapatan' => match ($param) {

                'UMUM' => view('dashboard.pages.pendapatan.umum'),

                'BPJS' => view('dashboard.pages.pendapatan.bpjs'),

                'JAMINAN' => view('dashboard.pages.pendapatan.jaminan'),

                'KERJASAMA' => view('dashboard.pages.pendapatan.kerjasama'),

                'LAIN' => view('dashboard.pages.pendapatan.lainlain'),

                default => abort(404),
            },

            'laporan' => view('dashboard.pages.laporan'),

            'rekening' => view('dashboard.pages.rekening'),

            'ruangan' => Auth::user()->isAdmin()
                ? view('dashboard.pages.ruangan')
                : abort(403),

            default => abort(404),
        };
    }
}
