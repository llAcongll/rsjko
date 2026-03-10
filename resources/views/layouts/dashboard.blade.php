<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>SIK BLUD EHD</title>

  {{-- FAVICON & PWA --}}
  <!-- Light mode -->
  <link rel="icon" type="image/png" href="{{ asset('favicon-light.png') }}?v=1.2" media="(prefers-color-scheme: light)">
  <!-- Dark mode -->
  <link rel="icon" type="image/png" href="{{ asset('favicon-dark.png') }}?v=1.2" media="(prefers-color-scheme: dark)">
  <!-- Fallback -->
  <link rel="icon" type="image/png" href="{{ asset('favicon-light.png') }}?v=1.2">
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}?v=1.2">
  <link rel="manifest" href="{{ asset('site.webmanifest') }}?v=1.2">
  <meta name="apple-mobile-web-app-title" content="SIPP BLUD EHD">

  <meta name="csrf-token" content="{{ csrf_token() }}">

  {{-- CSS --}}
  {{-- GOOGLE FONTS & ICONS --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@700&display=swap"
    rel="stylesheet">
  <script src="https://unpkg.com/@phosphor-icons/web"></script>

  <link rel="stylesheet" href="{{ asset('css/dashboard/base.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard/dashboard.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard/laporan.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard/rekening.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard/master.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard/ruangan.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard/perusahaan.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard/mou.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard/users.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard/pendapatan-umum.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard/pendapatan-bpjs.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard/anggaran.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard/pengeluaran.css') }}">

  <script>
    window.userRole = "{{ auth()->user()->role }}";
    window.userPermissions = {!! json_encode(auth()->user()->permissions ?? []) !!};
    window.isAdmin = {{ auth()->user()->isAdmin() ? 'true' : 'false' }};
    window.tahunAnggaran = "{{ session('tahun_anggaran') }}";

    window.hasPermission = function (p) {
      if (window.isAdmin) return true;
      return (window.userPermissions || []).includes(p);
    };

    // Global toast function placeholder if not defined
    if (typeof window.showToast !== 'function') {
      window.showToast = function (msg, type = 'info') {
        const toast = document.getElementById('toast');
        if (toast) {
          toast.innerText = msg;
          toast.className = 'toast show ' + type;
          setTimeout(() => { toast.className = 'toast'; }, 3000);
        } else {
          alert(msg);
        }
      };
    }
  </script>
</head>

<body>

  {{-- MOBILE HEADER --}}
  <div class="mobile-header">
    <div class="mobile-logo" style="display: flex; align-items: center; gap: 10px;">
      <i class="ph ph-buildings" style="font-size: 28px; color: #0f172a;"></i>
      <span>RSJKO EHD</span>
    </div>
    <button class="menu-toggle" onclick="toggleSidebar()">
      <i class="ph ph-list"></i>
    </button>
  </div>

  <div class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <div class="logo-wrapper">
        <div class="hospital-icon-wrapper">
          <i class="ph ph-buildings"></i>
        </div>
        <span>RSJKO EHD</span>
      </div>
    </div>

    <div class="sidebar-content">
      {{-- 1. DASHBOARD --}}
      @if(auth()->user()->hasPermission('DASHBOARD_VIEW'))
        <button onclick="openDashboard(this)">
          <i class="ph ph-chart-pie"></i>
          <span>Dashboard</span>
        </button>
      @endif

      {{-- 2. PERENCANAAN --}}
      @php
        $hasPerencanaan = auth()->user()->isAdmin() ||
          auth()->user()->hasPermission('KODE_REKENING_PENDAPATAN_VIEW') ||
          auth()->user()->hasPermission('KODE_REKENING_PENGELUARAN_VIEW') ||
          auth()->user()->hasPermission('ANGGARAN_PENDAPATAN_VIEW') ||
          auth()->user()->hasPermission('ANGGARAN_PENGELUARAN_VIEW');
      @endphp
      @if($hasPerencanaan)
        <button id="btnPerencanaan" onclick="togglePerencanaan(this)">
          <i class="ph ph-clipboard-text"></i>
          <span>Perencanaan</span>
          <i class="ph ph-caret-down dropdown-icon"></i>
        </button>
        <div class="submenu-child" id="submenuPerencanaan">
          @if(auth()->user()->hasPermission('KODE_REKENING_PENDAPATAN_VIEW'))
            <button onclick="openRekening(this, 'PENDAPATAN')">
              <i class="ph ph-list-numbers"></i>
              <span>Rek. Pendapatan</span>
            </button>
          @endif
          @if(auth()->user()->hasPermission('KODE_REKENING_PENGELUARAN_VIEW'))
            <button onclick="openRekening(this, 'PENGELUARAN')">
              <i class="ph ph-list-numbers"></i>
              <span>Rek. Pengeluaran</span>
            </button>
          @endif
          @if(auth()->user()->hasPermission('ANGGARAN_PENDAPATAN_VIEW'))
            <button onclick="openAnggaran(this, 'PENDAPATAN')">
              <i class="ph ph-money"></i>
              <span>Angg. Pendapatan</span>
            </button>
          @endif
          @if(auth()->user()->hasPermission('ANGGARAN_PENGELUARAN_VIEW'))
            <button onclick="openAnggaran(this, 'PENGELUARAN')">
              <i class="ph ph-hand-coins"></i>
              <span>Angg. Pengeluaran</span>
            </button>
          @endif
        </div>
      @endif

      {{-- 3. PENDAPATAN --}}
      @php
        $hasPendapatan = auth()->user()->isAdmin() ||
          auth()->user()->hasPermission('PENDAPATAN_UMUM_VIEW') ||
          auth()->user()->hasPermission('PENDAPATAN_BPJS_VIEW') ||
          auth()->user()->hasPermission('PENDAPATAN_JAMINAN_VIEW') ||
          auth()->user()->hasPermission('PENDAPATAN_KERJA_VIEW') ||
          auth()->user()->hasPermission('PENDAPATAN_LAIN_VIEW') ||
          auth()->user()->hasPermission('PENYESUAIAN_VIEW') ||
          auth()->user()->hasPermission('PIUTANG_VIEW');
      @endphp
      @if($hasPendapatan)
        <button id="btnPendapatan" onclick="togglePendapatan(this)">
          <i class="ph ph-coins"></i>
          <span>Pendapatan</span>
          <i class="ph ph-caret-down dropdown-icon"></i>
        </button>
        <div class="submenu-child" id="submenuPendapatan">
          @if(auth()->user()->hasPermission('PENDAPATAN_UMUM_VIEW'))
            <button onclick="openPendapatanUmum(this)">
              <i class="ph ph-person"></i>
              <span>Pendapatan Umum</span>
            </button>
          @endif
          @if(auth()->user()->hasPermission('PENDAPATAN_BPJS_VIEW'))
            <button onclick="openPendapatanBpjs(this)">
              <i class="ph ph-cardholder"></i>
              <span>Pendapatan BPJS</span>
            </button>
          @endif
          @if(auth()->user()->hasPermission('PENDAPATAN_JAMINAN_VIEW'))
            <button onclick="openPendapatanJaminan(this)">
              <i class="ph ph-shield-check"></i>
              <span>Pendapatan Jaminan</span>
            </button>
          @endif
          @if(auth()->user()->hasPermission('PENDAPATAN_KERJA_VIEW'))
            <button onclick="openPendapatanKerjasama(this)">
              <i class="ph ph-handshake"></i>
              <span>Kerjasama</span>
            </button>
          @endif
          @if(auth()->user()->hasPermission('PENDAPATAN_LAIN_VIEW'))
            <button onclick="openPendapatanLain(this)">
              <i class="ph ph-coins"></i>
              <span>Pend. Lain-lain</span>
            </button>
          @endif
          @if(auth()->user()->hasPermission('PIUTANG_VIEW'))
            <button onclick="openPiutang(this)">
              <i class="ph ph-credit-card"></i>
              <span>Piutang</span>
            </button>
          @endif
          @if(auth()->user()->hasPermission('PENYESUAIAN_VIEW'))
            <button onclick="openPenyesuaian(this)">
              <i class="ph ph-scissors"></i>
              <span>Pelunasan & Potongan</span>
            </button>
          @endif
        </div>
      @endif

      {{-- 4. KAS PENDAPATAN --}}
      @php
        $hasKasPend = auth()->user()->isAdmin() ||
          auth()->user()->hasPermission('REKKOR_VIEW') ||
          auth()->user()->hasPermission('BKU_PENDAPATAN_VIEW');
      @endphp
      @if($hasKasPend)
        <button id="btnKasPend" onclick="toggleKasPend(this)">
          <i class="ph ph-bank"></i>
          <span>Kas Pendapatan</span>
          <i class="ph ph-caret-down dropdown-icon"></i>
        </button>
        <div class="submenu-child" id="submenuKasPend">
          @if(auth()->user()->hasPermission('REKKOR_VIEW'))
            <button onclick="openRekeningKoran(this)">
              <i class="ph ph-article"></i>
              <span>Rekening Koran</span>
            </button>
          @endif
          @if(auth()->user()->hasPermission('BKU_PENDAPATAN_VIEW'))
            <button onclick="openIncomeCashBook(this)">
              <i class="ph ph-notebook"></i>
              <span>BKU Pendapatan</span>
            </button>
          @endif
        </div>
      @endif

      {{-- 5. PENGELUARAN --}}
      @php
        $hasPengeluaran = auth()->user()->isAdmin() ||
          auth()->user()->hasPermission('BELANJA_VIEW') ||
          auth()->user()->hasPermission('SPP_VIEW') ||
          auth()->user()->hasPermission('SPM_VIEW') ||
          auth()->user()->hasPermission('SP2D_VIEW');
      @endphp
      @if($hasPengeluaran)
        <button id="btnPengeluaran" onclick="togglePengeluaran(this)">
          <i class="ph ph-hand-coins"></i>
          <span>Pengeluaran</span>
          <i class="ph ph-caret-down dropdown-icon"></i>
        </button>
        <div class="submenu-child" id="submenuPengeluaran">
          @if(auth()->user()->hasPermission('BELANJA_VIEW'))
            <button onclick="openExpenditure(this)">
              <i class="ph ph-receipt"></i>
              <span>Belanja</span>
            </button>
          @endif
          @if(auth()->user()->hasPermission('SPP_VIEW'))
            <button onclick="openSpp(this)">
              <i class="ph ph-file-text"></i>
              <span>SPP</span>
            </button>
          @endif
          @if(auth()->user()->hasPermission('SPM_VIEW'))
            <button onclick="openSpm(this)">
              <i class="ph ph-seal-check"></i>
              <span>SPM</span>
            </button>
          @endif
          @if(auth()->user()->hasPermission('SP2D_VIEW'))
            <button onclick="openSp2d(this)">
              <i class="ph ph-check-circle"></i>
              <span>SP2D (Pencairan Dana)</span>
            </button>
          @endif
        </div>
      @endif

      {{-- 6. KAS PENGELUARAN --}}
      @php
        $hasKasPeng = auth()->user()->isAdmin() ||
          auth()->user()->hasPermission('REK_KORAN_PENG_VIEW') ||
          auth()->user()->hasPermission('BKU_PENGELUARAN_VIEW');
      @endphp
      @if($hasKasPeng)
        <button id="btnKasPeng" onclick="toggleKasPeng(this)">
          <i class="ph ph-wallet"></i>
          <span>Kas Pengeluaran</span>
          <i class="ph ph-caret-down dropdown-icon"></i>
        </button>
        <div class="submenu-child" id="submenuKasPeng">
          @if(auth()->user()->hasPermission('REK_KORAN_PENG_VIEW'))
            <button onclick="openRekeningKoranPengeluaran(this)">
              <i class="ph ph-article"></i>
              <span>Rekening Koran</span>
            </button>
          @endif
          @if(auth()->user()->hasPermission('BKU_PENGELUARAN_VIEW'))
            <button onclick="openTreasurerCash(this)">
              <i class="ph ph-notebook"></i>
              <span>BKU Pengeluaran</span>
            </button>
          @endif
        </div>
      @endif

      {{-- 7. LAPORAN KEUANGAN --}}
      @php
        $hasLaporan = auth()->user()->isAdmin() ||
          auth()->user()->hasPermission('LAP_PENDAPATAN_VIEW') ||
          auth()->user()->hasPermission('LAP_PENGELUARAN_VIEW') ||
          auth()->user()->hasPermission('LAP_LRA_VIEW') ||
          auth()->user()->hasPermission('LAP_LO_VIEW') ||
          auth()->user()->hasPermission('LAP_NERACA_VIEW') ||
          auth()->user()->hasPermission('LAP_LAK_VIEW') ||
          auth()->user()->hasPermission('LAP_CALK_VIEW');
      @endphp
      @if($hasLaporan)
        <button id="btnLaporan" onclick="toggleLaporan(this)">
          <i class="ph ph-chart-bar"></i>
          <span>Laporan</span>
          <i class="ph ph-caret-down dropdown-icon"></i>
        </button>
        <div class="submenu-child" id="submenuLaporan">
          @if(auth()->user()->hasPermission('LAP_PENDAPATAN_VIEW'))
            <button onclick="openLaporan('PENDAPATAN', this)">
              <span>Lap. Pendapatan</span>
            </button>
          @endif
          @if(auth()->user()->hasPermission('LAP_PENGELUARAN_VIEW'))
            <button onclick="openLaporan('PENGELUARAN', this)">
              <span>Lap. Pengeluaran</span>
            </button>
          @endif
          @if(auth()->user()->hasPermission('LAP_LRA_VIEW'))
            <button onclick="openLaporan('ANGGARAN', this)">
              <span>Lap. Realisasi (LRA)</span>
            </button>
          @endif
          @if(auth()->user()->hasPermission('LAP_LO_VIEW'))
            <button onclick="openLaporan('LO', this)">
              <span>Lap. Operasional (LO)</span>
            </button>
          @endif
          @if(auth()->user()->hasPermission('LAP_NERACA_VIEW'))
            <button onclick="openLaporan('NERACA', this)">
              <span>Neraca</span>
            </button>
          @endif
          @if(auth()->user()->hasPermission('LAP_LAK_VIEW'))
            <button onclick="openLaporan('LAK', this)">
              <span>Arus Kas (LAK)</span>
            </button>
          @endif
          @if(auth()->user()->hasPermission('LAP_CALK_VIEW'))
            <button onclick="openLaporan('CALK', this)">
              <span>CaLK</span>
            </button>
          @endif
        </div>
      @endif

      {{-- 8. PENGESAHAN --}}
      @php
        $hasPengesahan = auth()->user()->isAdmin() ||
          auth()->user()->hasPermission('SP3BP_VIEW') ||
          auth()->user()->hasPermission('LRKB_VIEW') ||
          auth()->user()->hasPermission('SPTJB_VIEW');
      @endphp
      @if($hasPengesahan)
        <button id="btnPengesahan" onclick="togglePengesahan(this)">
          <i class="ph ph-seal-check"></i>
          <span>Pengesahan</span>
          <i class="ph ph-caret-down dropdown-icon"></i>
        </button>
        <div class="submenu-child" id="submenuPengesahan">
          @if(auth()->user()->hasPermission('SP3BP_VIEW'))
            <button onclick="openPengesahan('SP3BP', this)">
              <i class="ph ph-file-text"></i>
              <span>SP3BP</span>
            </button>
          @endif
          @if(auth()->user()->hasPermission('LRKB_VIEW'))
            <button onclick="openPengesahan('LRKB', this)">
              <i class="ph ph-clipboard-text"></i>
              <span>LRKB</span>
            </button>
          @endif
          @if(auth()->user()->hasPermission('SPTJB_VIEW'))
            <button onclick="openPengesahan('SPTJB', this)">
              <i class="ph ph-signature"></i>
              <span>SPTJB</span>
            </button>
          @endif
        </div>
      @endif
      @php
        $showMasterSystem =
          auth()->user()->isAdmin() ||
          auth()->user()->hasPermission('RUANGAN_VIEW') ||
          auth()->user()->hasPermission('PERUSAHAAN_VIEW') ||
          auth()->user()->hasPermission('MOU_VIEW') ||
          auth()->user()->hasPermission('PENANDATANGAN_VIEW') ||
          auth()->user()->hasPermission('USER_VIEW') ||
          auth()->user()->hasPermission('LOG_VIEW');
      @endphp
      @if($showMasterSystem)
        <div class="menu-divider">Master & System</div>
      @endif

      {{-- 9. MASTER DATA --}}
      @php
        $hasMaster = auth()->user()->isAdmin() ||
          auth()->user()->hasPermission('RUANGAN_VIEW') ||
          auth()->user()->hasPermission('PERUSAHAAN_VIEW') ||
          auth()->user()->hasPermission('MOU_VIEW') ||
          auth()->user()->hasPermission('PENANDATANGAN_VIEW');
      @endphp
      @if($hasMaster)
        <button id="btnMaster" onclick="toggleMaster(this)">
          <i class="ph ph-database"></i>
          <span>Master Data</span>
          <i class="ph ph-caret-down dropdown-icon"></i>
        </button>
        <div class="submenu-child" id="submenuMaster">
          @if(auth()->user()->hasPermission('RUANGAN_VIEW'))
            <button onclick="openRuangan(this)">
              <i class="ph ph-buildings"></i>
              <span>Ruangan</span>
            </button>
          @endif
          @if(auth()->user()->hasPermission('PERUSAHAAN_VIEW'))
            <button onclick="openPerusahaanPage(this)">
              <i class="ph ph-factory"></i>
              <span>Perusahaan</span>
            </button>
          @endif
          @if(auth()->user()->hasPermission('MOU_VIEW'))
            <button onclick="openMouPage(this)">
              <i class="ph ph-file-text"></i>
              <span>MOU</span>
            </button>
          @endif
          @if(auth()->user()->hasPermission('PENANDATANGAN_VIEW'))
            <button onclick="openPenandaTangan(this)">
              <i class="ph ph-signature"></i>
              <span>Penanda Tangan</span>
            </button>
          @endif
        </div>
      @endif

      {{-- 10. SYSTEM --}}
      @php
        $hasSystem = auth()->user()->isAdmin() ||
          auth()->user()->hasPermission('USER_VIEW') ||
          auth()->user()->hasPermission('LOG_VIEW');
      @endphp
      @if($hasSystem)
        <button id="btnSystem" onclick="toggleSystem(this)">
          <i class="ph ph-desktop"></i>
          <span>System</span>
          <i class="ph ph-caret-down dropdown-icon"></i>
        </button>
        <div class="submenu-child" id="submenuSystem">
          @if(auth()->user()->hasPermission('USER_VIEW'))
            <button onclick="openUsers(this)">
              <i class="ph ph-users"></i>
              <span>User Management</span>
            </button>
          @endif
          @if(auth()->user()->hasPermission('LOG_VIEW'))
            <button onclick="openActivityLogs(this)">
              <i class="ph ph-article"></i>
              <span>Activity Log</span>
            </button>
          @endif
        </div>
      @endif
    </div>

    <div class="sidebar-footer">
      <div class="user-info">
        <div class="user-avatar">
          <i class="ph ph-user"></i>
        </div>
        <div class="user-details">
          <p class="username">{{ auth()->check() ? auth()->user()->username : '-' }}</p>
          <p class="role">{{ auth()->check() && auth()->user()->isAdmin() ? 'Administrator' : 'Staff' }}</p>
        </div>
      </div>

      <form id="logoutForm" method="POST" action="{{ url('logout') }}">
        @csrf
        <button type="button" class="btn-logout" onclick="confirmLogout()">
          <i class="ph ph-sign-out"></i>
          <span>Keluar</span>
        </button>
      </form>
    </div>
  </div>

  <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

  <div class="main">
    <div id="mainContent">
      @yield('content')
    </div>
  </div>

  <div id="toast" class="toast"></div>

  <!-- GLOBAL LOADER OVERLAY -->
  <div id="globalLoader" class="confirm-overlay" style="z-index: 9000; flex-direction: column;">
    <i class="ph ph-spinner animate-spin" style="font-size: 40px; color: #fff;"></i>
    <span
      style="margin-top: 12px; color: #fff; font-size: 14px; font-weight: 600; letter-spacing: 0.5px;">Memproses...</span>
  </div>

  {{-- MODAL DELETE --}}
  @include('dashboard.partials.confirm-delete')

  {{-- MODAL PREVIEW --}}
  @include('dashboard.partials.preview')
  @include('dashboard.partials.report-preview-modal')

  {{-- MODAL USER --}}
  @include('dashboard.partials.user-form')

  {{-- MODAL RUANGAN --}}
  @include('dashboard.partials.ruangan-form')

  {{-- MODAL PERUSAHAAN --}}
  @include('dashboard.partials.perusahaan-form')

  {{-- MODAL MOU --}}
  @include('dashboard.partials.mou-form')

  {{-- MODAL PENANDA TANGAN --}}
  @include('dashboard.partials.penanda-tangan-form')

  {{-- MODAL REKENING --}}
  @include('dashboard.partials.rekening-form')
  @include('dashboard.partials.bank-ledger-form')

  {{-- MODAL PIUTANG --}}
  @include('dashboard.partials.piutang-form')
  @include('dashboard.partials.piutang-detail')
  @include('dashboard.partials.penyesuaian-form')

  {{-- MODAL PENDAPATAN UMUM --}}
  @include('dashboard.partials.pendapatan-umum-form')

  {{-- MODAL PENDAPATAN BPJS --}}
  @include('dashboard.partials.pendapatan-bpjs-form')

  {{-- MODAL LAINNYA --}}
  @include('dashboard.partials.pendapatan-jaminan-form')
  @include('dashboard.partials.pendapatan-kerjasama-form')
  @include('dashboard.partials.pendapatan-lain-form')

  <!-- GLOBAL LIBS -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  {{-- MODAL KODE REKENING --}}
  @include('dashboard.partials.kode-rekening-form')

  {{-- MODAL ANGGARAN REKENING --}}
  @include('dashboard.partials.anggaran-form')

  {{-- MODAL PENGELUARAN --}}
  @include('dashboard.partials.pengeluaran-form')
  @include('dashboard.partials.pengeluaran-detail')
  @include('dashboard.partials.disbursement-detail')
  @include('dashboard.partials.pencairan-form')
  @include('dashboard.partials.spj-form')
  @include('dashboard.partials.sp3bp-modal')
  @include('dashboard.partials.lrkb-modal')

  {{-- MODAL REKENING EXTRA --}}
  @include('dashboard.partials.rekening-extra-modals')

  {{-- MODAL IMPORT & BULK DELETE --}}
  @include('dashboard.partials.pendapatan-umum-import')
  @include('dashboard.partials.pendapatan-umum-bulk-delete')
  @include('dashboard.partials.pendapatan-bpjs-import')
  @include('dashboard.partials.pendapatan-bpjs-bulk-delete')
  @include('dashboard.partials.pendapatan-jaminan-import')
  @include('dashboard.partials.pendapatan-jaminan-bulk-delete')
  @include('dashboard.partials.pendapatan-kerjasama-import')
  @include('dashboard.partials.pendapatan-kerjasama-bulk-delete')
  @include('dashboard.partials.pendapatan-lain-import')
  @include('dashboard.partials.pendapatan-lain-bulk-delete')

  {{-- MODAL PENDAPATAN MASTER --}}
  @include('dashboard.partials.pendapatan-master-modals')

  {{-- SYSTEM & LOG MODALS --}}
  @include('dashboard.partials.log-modals')
  @include('dashboard.partials.rekening-pengeluaran-extra-modals')
  @include('dashboard.partials.adjustment-form')
  @include('dashboard.partials.bank-ledger-form')

  {{-- JS --}}
  <script src="{{ asset('js/base.js') }}?v={{ filemtime(public_path('js/base.js')) }}"></script>
  <script src="{{ asset('js/dashboard/app.js') }}?v={{ filemtime(public_path('js/dashboard/app.js')) }}"></script>
  <script
    src="{{ asset('js/dashboard/dashboard.js') }}?v={{ filemtime(public_path('js/dashboard/dashboard.js')) }}"></script>
  <script src="{{ asset('js/dashboard/users.js') }}?v={{ filemtime(public_path('js/dashboard/users.js')) }}"></script>
  <script
    src="{{ asset('js/dashboard/ruangan.js') }}?v={{ filemtime(public_path('js/dashboard/ruangan.js')) }}"></script>
  <script
    src="{{ asset('js/dashboard/perusahaan.js') }}?v={{ filemtime(public_path('js/dashboard/perusahaan.js')) }}"></script>
  <script src="{{ asset('js/dashboard/mou.js') }}?v={{ filemtime(public_path('js/dashboard/mou.js')) }}"></script>
  <script
    src="{{ asset('js/dashboard/penanda-tangan.js') }}?v={{ filemtime(public_path('js/dashboard/penanda-tangan.js')) }}"></script>
  <script
    src="{{ asset('js/dashboard/rekening.js') }}?v={{ filemtime(public_path('js/dashboard/rekening.js')) }}"></script>
  <script
    src="{{ asset('js/dashboard/piutang.js') }}?v={{ filemtime(public_path('js/dashboard/piutang.js')) }}"></script>
  <script
    src="{{ asset('js/dashboard/penyesuaian.js') }}?v={{ filemtime(public_path('js/dashboard/penyesuaian.js')) }}"></script>
  <script
    src="{{ asset('js/dashboard/pendapatan-umum.js') }}?v={{ filemtime(public_path('js/dashboard/pendapatan-umum.js')) }}"></script>
  <script
    src="{{ asset('js/dashboard/pendapatan-bpjs.js') }}?v={{ filemtime(public_path('js/dashboard/pendapatan-bpjs.js')) }}"></script>
  <script
    src="{{ asset('js/dashboard/jaminan.js') }}?v={{ filemtime(public_path('js/dashboard/jaminan.js')) }}"></script>
  <script
    src="{{ asset('js/dashboard/kerjasama.js') }}?v={{ filemtime(public_path('js/dashboard/kerjasama.js')) }}"></script>
  <script
    src="{{ asset('js/dashboard/lain-lain.js') }}?v={{ filemtime(public_path('js/dashboard/lain-lain.js')) }}"></script>
  <script
    src="{{ asset('js/dashboard/laporan.js') }}?v={{ filemtime(public_path('js/dashboard/laporan.js')) }}"></script>
  <script
    src="{{ asset('js/dashboard/kode-rekening.js') }}?v={{ filemtime(public_path('js/dashboard/kode-rekening.js')) }}"></script>
  <script
    src=" {{ asset('js/dashboard/anggaran-rekening.js') }}?v={{ filemtime(public_path('js/dashboard/anggaran-rekening.js')) }}"></script>
  <script
    src="{{ asset('js/dashboard/pengeluaran.js') }}?v={{ filemtime(public_path('js/dashboard/pengeluaran.js')) }}"></script>
  <script
    src="{{ asset('js/dashboard/treasurer.js') }}?v={{ filemtime(public_path('js/dashboard/treasurer.js')) }}"></script>
  <script
    src="{{ asset('js/dashboard/bank-ledger.js') }}?v={{ filemtime(public_path('js/dashboard/bank-ledger.js')) }}"></script>
  <script
    src="{{ asset('js/dashboard/universal-table.js') }}?v={{ filemtime(public_path('js/dashboard/universal-table.js')) }}"></script>
  <script src="{{ asset('js/dashboard/logs.js') }}?v={{ filemtime(public_path('js/dashboard/logs.js')) }}"></script>
  <script
    src="{{ asset('js/dashboard/income-cash-book.js') }}?v={{ filemtime(public_path('js/dashboard/income-cash-book.js')) }}"></script>

  <!-- Modal Konfirmasi Universal (UI Berbasis Aksi) -->
  <div id="modalConfirmAction" class="confirm-overlay">
    <div class="confirm-box" style="max-width: 400px; text-align: center; padding: 30px;">
      <div id="confirmActionIcon" style="font-size: 3.5rem; margin-bottom: 15px;"></div>
      <h3 id="confirmActionTitle" style="margin-bottom: 12px; font-size: 1.25rem; font-weight: 700; color: #0f172a;">
        Konfirmasi
      </h3>
      <div id="confirmActionMessage" style="color: #64748b; font-size: 14px; margin-bottom: 30px; line-height: 1.6;">
      </div>
      <div class="confirm-actions" style="justify-content: center; display: flex; gap: 12px;">
        <button type="button" class="btn-secondary" onclick="closeConfirmActionModal()"
          style="flex: 1; padding: 10px; font-weight: 600;">
          Batal
        </button>
        <button type="button" id="btnConfirmActionProceed" class="btn-primary"
          style="flex: 1.5; padding: 10px; font-weight: 700;">
          Lanjutkan
        </button>
      </div>
    </div>
  </div>

  {{-- AUTO LOGOUT TIMER (30 MINS) --}}
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      let inactivityTimer;
      const timeoutMillis = 30 * 60 * 1000; // 30 minutes
      const logoutForm = document.getElementById('logoutForm');

      if (!logoutForm) return;

      function resetTimer() {
        clearTimeout(inactivityTimer);
        inactivityTimer = setTimeout(function () {
          logoutForm.submit();
        }, timeoutMillis);
      }

      window.onload = resetTimer;
      document.onmousemove = resetTimer;
      document.onkeypress = resetTimer;
      document.onclick = resetTimer;
      document.onscroll = resetTimer;
    });
  </script>
</body>

</html>