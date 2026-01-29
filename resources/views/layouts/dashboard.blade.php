<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>

  <meta name="csrf-token" content="{{ csrf_token() }}">

  {{-- CSS --}}
  <link rel="stylesheet" href="{{ asset('css/dashboard/base.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard/dashboard.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard/laporan.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard/rekening.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard/master.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard/ruangan.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard/users.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard/pendapatan-umum.css') }}">

</head>

<body>

<div class="sidebar">
  <h3><center>RSJKO EHD</center></h3>

  <button onclick="openDashboard(this)">📊 Dashboard</button>

  <button onclick="openRekening(this)">📒 Rekening Koran</button>

  <button id="btnPendapatan" onclick="togglePendapatan(this)">💰 Pendapatan</button>

  <div class="submenu-child" id="submenuPendapatan">
    <button onclick="openPendapatan('UMUM', this)">• Pasien Umum</button>
    <button onclick="openPendapatan('BPJS', this)">• BPJS</button>
    <button onclick="openPendapatan('JAMINAN', this)">• Jaminan</button>
    <button onclick="openPendapatan('KERJASAMA', this)">• Kerjasama</button>
    <button onclick="openPendapatan('LAIN', this)">• Lain-lain</button>
  </div>

  <button onclick="openLaporan(this)">📊 Laporan</button>

  @if(auth()->check() && auth()->user()->isAdmin())
    <button onclick="openRuangan(this)">🏥 Ruangan</button>
    <button onclick="openUsers(this)">👤 Users</button>
  @endif

  {{-- LOGIN INFO --}}
  <div id="loginInfo" style="margin-top:auto;padding:12px 14px;font-size:13px">
    👤 {{ auth()->check() ? auth()->user()->username : '-' }}
  </div>

  {{-- LOGOUT --}}
<form id="logoutForm" method="POST" action="/logout">
  @csrf
  <button type="button" class="logout" onclick="confirmLogout()">🚪 Logout</button>
</form>
</div>

<div class="main">
  <div id="mainContent">
    @yield('content')
  </div>
</div>

<div id="toast" class="toast"></div>

{{-- MODAL DELETE --}}
@include('dashboard.partials.confirm-delete')

{{-- MODAL PREVIEW --}}
@include('dashboard.partials.preview')

{{-- MODAL USER --}}
@include('dashboard.partials.user-form')

{{-- MODAL RUANGAN --}}
@include('dashboard.partials.ruangan-form')

{{-- MODAL REKENING --}}
@include('dashboard.partials.rekening-form')

{{-- MODAL PENDAPATAN UMUM --}}
@include('dashboard.partials.pendapatan-umum-form')

{{-- JS --}}
<script src="{{ asset('js/base.js') }}"></script>
<script src="{{ asset('js/dashboard/app.js') }}"></script>
<script src="{{ asset('js/dashboard/users.js') }}"></script>
<script src="{{ asset('js/dashboard/ruangan.js') }}"></script>
<script src="{{ asset('js/dashboard/rekening.js') }}"></script>
<script src="{{ asset('js/dashboard/pendapatan-umum.js') }}"></script>

</body>
</html>
