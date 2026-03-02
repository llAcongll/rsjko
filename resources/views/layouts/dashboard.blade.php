<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>SIPP BLUD EHD</title>

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
      return window.userPermissions.includes(p);
    };
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
      <button onclick="openDashboard(this)">
        <i class="ph ph-chart-pie-slice"></i>
        <span>Dashboard</span>
      </button>

      @php
        $hasAnyPerencanaan = auth()->user()->hasPermission('KODE_REKENING_VIEW') ||
          auth()->user()->hasPermission('KODE_REKENING_PENDAPATAN_VIEW') ||
          auth()->user()->hasPermission('KODE_REKENING_PENGELUARAN_VIEW') ||
          auth()->user()->isAdmin();
      @endphp

      @if($hasAnyPerencanaan)
        <button id="btnPerencanaan" onclick="togglePerencanaan(this)">
          <i class="ph ph-clipboard-text"></i>
          <span>Perencanaan</span>
          <i class="ph ph-caret-down dropdown-icon"></i>
        </button>

        <div class="submenu-child" id="submenuPerencanaan">
          @php
            $hasPendapatanMaster = auth()->user()->hasPermission('KODE_REKENING_PENDAPATAN_VIEW') || auth()->user()->hasPermission('KODE_REKENING_VIEW');
            $hasPengeluaranMaster = auth()->user()->hasPermission('KODE_REKENING_PENGELUARAN_VIEW') || auth()->user()->hasPermission('KODE_REKENING_VIEW');
          @endphp

          @if($hasPendapatanMaster)
            <div class="submenu-header">Pendapatan</div>
            <button onclick="openKodeRekening('PENDAPATAN', this)">
              <i class="ph ph-list-numbers"></i>
              <span>Kode Rekening</span>
            </button>
            <button onclick="openAnggaranRekening('PENDAPATAN', this)">
              <i class="ph ph-calendar-check"></i>
              <span>Anggaran</span>
            </button>
          @endif

          @if($hasPengeluaranMaster)
            <div class="submenu-header">Pengeluaran</div>
            <button onclick="openKodeRekening('PENGELUARAN', this)">
              <i class="ph ph-list-numbers"></i>
              <span>Kode Rekening</span>
            </button>
            <button onclick="openAnggaranRekening('PENGELUARAN', this)">
              <i class="ph ph-calendar-check"></i>
              <span>Anggaran</span>
            </button>
          @endif
        </div>
      @endif


      @php
        $hasAnyPendapatan =
          auth()->user()->hasPermission('PENDAPATAN_UMUM_VIEW') || auth()->user()->hasPermission('PENDAPATAN_UMUM') ||
          auth()->user()->hasPermission('PENDAPATAN_BPJS_VIEW') || auth()->user()->hasPermission('PENDAPATAN_BPJS') ||
          auth()->user()->hasPermission('PENDAPATAN_JAMINAN_VIEW') || auth()->user()->hasPermission('PENDAPATAN_JAMINAN') ||
          auth()->user()->hasPermission('PENDAPATAN_KERJA_VIEW') || auth()->user()->hasPermission('PENDAPATAN_KERJA') ||
          auth()->user()->hasPermission('PENDAPATAN_LAIN_VIEW') || auth()->user()->hasPermission('PENDAPATAN_LAIN') ||
          auth()->user()->hasPermission('REKENING_VIEW') ||
          auth()->user()->hasPermission('PIUTANG_VIEW') ||
          auth()->user()->hasPermission('PENYESUAIAN_VIEW') ||
          auth()->user()->isAdmin();
      @endphp

      @if($hasAnyPendapatan)
        <button id="btnPendapatan" onclick="togglePendapatan(this)">
          <i class="ph ph-coins"></i>
          <span>Pendapatan</span>
          <i class="ph ph-caret-down dropdown-icon"></i>
        </button>

        <div class="submenu-child" id="submenuPendapatan">
          <div class="submenu-header">Kelola Kas & Piutang</div>
          @if(auth()->user()->hasPermission('REKENING_VIEW'))
            <button onclick="openRekening(this)">
              <i class="ph ph-notebook"></i>
              <span>Rekening Koran Pendapatan</span>
            </button>
          @endif
          @if(auth()->user()->hasPermission('PIUTANG_VIEW'))
            <button onclick="openPiutang(this)">
              <i class="ph ph-invoice"></i>
              <span>Piutang</span>
            </button>
          @endif
          @if(auth()->user()->hasPermission('PENYESUAIAN_VIEW'))
            <button onclick="openPenyesuaian(this)">
              <i class="ph ph-scissors"></i>
              <span>Potongan & Adm Bank</span>
            </button>
          @endif

          <div class="submenu-header">Rincian Pasien</div>
          @if(auth()->user()->hasPermission('PENDAPATAN_UMUM_VIEW') || auth()->user()->hasPermission('PENDAPATAN_UMUM'))
            <button onclick="openPendapatan('UMUM', this)">
              <i class="ph ph-user"></i>
              <span>Pasien Umum</span>
            </button>
          @endif
          @if(auth()->user()->hasPermission('PENDAPATAN_BPJS_VIEW') || auth()->user()->hasPermission('PENDAPATAN_BPJS'))
            <button onclick="openPendapatan('BPJS', this)">
              <i class="ph ph-cardholder"></i>
              <span>BPJS</span>
            </button>
          @endif
          @if(auth()->user()->hasPermission('PENDAPATAN_JAMINAN_VIEW') || auth()->user()->hasPermission('PENDAPATAN_JAMINAN'))
            <button onclick="openPendapatan('JAMINAN', this)">
              <i class="ph ph-shield-check"></i>
              <span>Jaminan</span>
            </button>
          @endif
          @if(auth()->user()->hasPermission('PENDAPATAN_KERJA_VIEW') || auth()->user()->hasPermission('PENDAPATAN_KERJA'))
            <button onclick="openPendapatan('KERJASAMA', this)">
              <i class="ph ph-handshake"></i>
              <span>Kerjasama</span>
            </button>
          @endif
          @if(auth()->user()->hasPermission('PENDAPATAN_LAIN_VIEW') || auth()->user()->hasPermission('PENDAPATAN_LAIN'))
            <button onclick="openPendapatan('LAIN', this)">
              <i class="ph ph-dots-three-circle"></i>
              <span>Lain-lain</span>
            </button>
          @endif
        </div>
      @endif

      @php
        $hasAnyPengeluaran = auth()->user()->hasPermission('PENGELUARAN_VIEW') ||
          auth()->user()->hasPermission('PENGELUARAN_SPP_VIEW') ||
          auth()->user()->hasPermission('PENGELUARAN_SPM_VIEW') ||
          auth()->user()->hasPermission('PENGELUARAN_SP2D_VIEW') ||
          auth()->user()->hasPermission('PENGELUARAN_CAIR_VIEW') ||
          auth()->user()->hasPermission('PENGELUARAN_RK_VIEW') ||
          auth()->user()->hasPermission('PENGELUARAN_SALDO_VIEW') ||
          auth()->user()->hasPermission('PENGELUARAN_BKU_VIEW') ||
          auth()->user()->isAdmin();
      @endphp

      @if($hasAnyPengeluaran)
        <button id="btnPengeluaran" onclick="togglePengeluaran(this)">
          <i class="ph ph-hand-coins"></i>
          <span>Pengeluaran</span>
          <i class="ph ph-caret-down dropdown-icon"></i>
        </button>

        <div class="submenu-child" id="submenuPengeluaran">
          <div class="submenu-header">Pencairan Dana</div>
          @if(auth()->user()->hasPermission('PENGELUARAN_SPP_VIEW') || auth()->user()->isAdmin())
            <button onclick="openSppPage(this)">
              <i class="ph ph-file-text"></i>
              <span>SPP</span>
            </button>
          @endif
          @if(auth()->user()->hasPermission('PENGELUARAN_SPM_VIEW') || auth()->user()->isAdmin())
            <button onclick="openSpmPage(this)">
              <i class="ph ph-seal-check"></i>
              <span>SPM</span>
            </button>
          @endif
          @if(auth()->user()->hasPermission('PENGELUARAN_SP2D_VIEW') || auth()->user()->isAdmin())
            <button onclick="openSp2dPage(this)">
              <i class="ph ph-check-circle"></i>
              <span>SP2D</span>
            </button>
          @endif
          @if(auth()->user()->hasPermission('PENGELUARAN_CAIR_VIEW') || auth()->user()->isAdmin())
            <button onclick="openPencairanPage(this)">
              <i class="ph ph-wallet"></i>
              <span>Pencairan</span>
            </button>
          @endif

          <div class="submenu-header">Kelola Kas</div>
          @if(auth()->user()->hasPermission('PENGELUARAN_RK_VIEW') || auth()->user()->isAdmin())
            <button onclick="openRekeningKoranPengeluaran(this)">
              <i class="ph ph-bank"></i>
              <span>Rekening Koran Pengeluaran</span>
            </button>
          @endif
          @if(auth()->user()->hasPermission('PENGELUARAN_SALDO_VIEW') || auth()->user()->isAdmin())
            <button onclick="openSaldoDana(this)">
              <i class="ph ph-piggy-bank"></i>
              <span>Saldo Dana</span>
            </button>
          @endif

          <div class="submenu-header">Laporan Kas</div>
          @if(auth()->user()->hasPermission('PENGELUARAN_BKU_VIEW') || auth()->user()->isAdmin())
            <button onclick="openTreasurerCash(this)">
              <i class="ph ph-book-open"></i>
              <span>Buku Kas Umum</span>
            </button>
          @endif
        </div>
      @endif


      @php
        $hasAnyLaporan = auth()->user()->hasPermission('LAPORAN_PENDAPATAN') ||
          auth()->user()->hasPermission('LAPORAN_REKON') ||
          auth()->user()->hasPermission('LAPORAN_PIUTANG') ||
          auth()->user()->hasPermission('LAPORAN_MOU') ||
          auth()->user()->hasPermission('LAPORAN_ANGGARAN') ||
          auth()->user()->hasPermission('LAPORAN_PENGELUARAN') ||
          auth()->user()->hasPermission('LAPORAN_VIEW') ||
          auth()->user()->isAdmin();
      @endphp

      @if($hasAnyLaporan)
        <button id="btnLaporan" onclick="toggleLaporan(this)">
          <i class="ph ph-chart-bar"></i>
          <span>Laporan Keuangan</span>
          <i class="ph ph-caret-down dropdown-icon"></i>
        </button>

        <div class="submenu-child" id="submenuLaporan">
          {{-- 1. Pendapatan --}}
          <div class="submenu-header">Pendapatan</div>
          @if(auth()->user()->hasPermission('LAPORAN_PENDAPATAN_VIEW') || auth()->user()->hasPermission('LAPORAN_VIEW') || auth()->user()->isAdmin())
            <button onclick="openLaporan('PENDAPATAN', this)">
              <i class="ph ph-money"></i>
              <span>Laporan Pendapatan</span>
            </button>
          @endif
          @if(auth()->user()->hasPermission('LAPORAN_REKON_VIEW') || auth()->user()->hasPermission('LAPORAN_VIEW') || auth()->user()->isAdmin())
            <button onclick="openLaporan('REKON', this)">
              <i class="ph ph-arrows-left-right"></i>
              <span>Laporan Rekonsiliasi</span>
            </button>
          @endif
          @if(auth()->user()->hasPermission('LAPORAN_PIUTANG_VIEW') || auth()->user()->hasPermission('LAPORAN_VIEW') || auth()->user()->isAdmin())
            <button onclick="openLaporan('PIUTANG', this)">
              <i class="ph ph-invoice"></i>
              <span>Laporan Piutang</span>
            </button>
          @endif
          @if(auth()->user()->hasPermission('LAPORAN_MOU_VIEW') || auth()->user()->hasPermission('LAPORAN_VIEW') || auth()->user()->isAdmin())
            <button onclick="openLaporan('MOU', this)">
              <i class="ph ph-file-text"></i>
              <span>Laporan MOU</span>
            </button>
          @endif

          {{-- 2. Belanja --}}
          <div class="submenu-header">Belanja</div>
          @if(auth()->user()->hasPermission('LAPORAN_PENGELUARAN_VIEW') || auth()->user()->hasPermission('LAPORAN_VIEW') || auth()->user()->isAdmin())
            <button onclick="openLaporan('PENGELUARAN', this)">
              <i class="ph ph-hand-coins"></i>
              <span>Laporan Pengeluaran</span>
            </button>
          @endif
          @if(auth()->user()->hasPermission('LAPORAN_ANGGARAN_VIEW') || auth()->user()->hasPermission('LAPORAN_VIEW') || auth()->user()->isAdmin())
            <button onclick="openLaporan('DPA', this)">
              <i class="ph ph-article"></i>
              <span>Laporan DPA</span>
            </button>
            <button onclick="openLaporan('ANGGARAN', this)">
              <i class="ph ph-chart-pie-slice"></i>
              <span>Realisasi Anggaran</span>
            </button>
          @endif
          @if(auth()->user()->hasPermission('LAPORAN_BKU_VIEW') || auth()->user()->hasPermission('LAPORAN_VIEW') || auth()->user()->isAdmin())
            <button onclick="openLaporan('BKU', this)">
              <i class="ph ph-book-open"></i>
              <span>Buku Kas Umum</span>
            </button>
          @endif
          @if(auth()->user()->hasPermission('LAPORAN_RKA') || auth()->user()->hasPermission('LAPORAN_VIEW'))
            <button onclick="toast('Laporan RKA dalam pengembangan', 'info')">
              <i class="ph ph-file-pdf"></i>
              <span>Laporan RKA</span>
            </button>
          @endif
          @if(auth()->user()->hasPermission('LAPORAN_RBA') || auth()->user()->hasPermission('LAPORAN_VIEW'))
            <button onclick="toast('Laporan RBA dalam pengembangan', 'info')">
              <i class="ph ph-file-text"></i>
              <span>Rencana Bisnis & Anggaran (RBA)</span>
            </button>
          @endif

          {{-- 3. Laporan Keuangan (SAP Akrual) --}}
          <div class="submenu-header">Laporan Keuangan (SAP Akrual)</div>
          @if(auth()->user()->hasPermission('LAPORAN_LO') || auth()->user()->hasPermission('LAPORAN_VIEW'))
            <button onclick="toast('Laporan Operasional (LO) dalam pengembangan', 'info')">
              <i class="ph ph-book"></i>
              <span>Laporan Operasional (LO)</span>
            </button>
          @endif
          @if(auth()->user()->hasPermission('LAPORAN_LPE') || auth()->user()->hasPermission('LAPORAN_VIEW'))
            <button onclick="toast('Laporan Perubahan Ekuitas (LPE) dalam pengembangan', 'info')">
              <i class="ph ph-graph"></i>
              <span>Laporan Perubahan Ekuitas (LPE)</span>
            </button>
          @endif
          @if(auth()->user()->hasPermission('LAPORAN_NERACA') || auth()->user()->hasPermission('LAPORAN_VIEW'))
            <button onclick="toast('Neraca dalam pengembangan', 'info')">
              <i class="ph ph-columns"></i>
              <span>Neraca</span>
            </button>
          @endif
          @if(auth()->user()->hasPermission('LAPORAN_LAK') || auth()->user()->hasPermission('LAPORAN_VIEW'))
            <button onclick="toast('Laporan Arus Kas (LAK) dalam pengembangan', 'info')">
              <i class="ph ph-currency-circle-dollar"></i>
              <span>Laporan Arus Kas (LAK)</span>
            </button>
          @endif
          @if(auth()->user()->hasPermission('LAPORAN_LPSAL') || auth()->user()->hasPermission('LAPORAN_VIEW'))
            <button onclick="toast('Laporan Perubahan SAL (LPSAL) dalam pengembangan', 'info')">
              <i class="ph ph-receipt"></i>
              <span>Laporan Perubahan SAL (LPSAL)</span>
            </button>
          @endif
          @if(auth()->user()->hasPermission('LAPORAN_CALK') || auth()->user()->hasPermission('LAPORAN_VIEW'))
            <button onclick="toast('Catatan Atas Laporan Keuangan (CALK) dalam pengembangan', 'info')">
              <i class="ph ph-notebook"></i>
              <span>Catatan Atas Laporan Keuangan (CALK)</span>
            </button>
          @endif
        </div>
      @endif

      @php
        $hasAnyPengesahan = auth()->user()->hasPermission('PENGESAHAN_VIEW') || auth()->user()->isAdmin();
      @endphp

      @if($hasAnyPengesahan)
        <button id="btnPengesahan" onclick="togglePengesahan(this)">
          <i class="ph ph-seal-check"></i>
          <span>Pengesahan</span>
          <i class="ph ph-caret-down dropdown-icon"></i>
        </button>

        <div class="submenu-child" id="submenuPengesahan">
          <div class="submenu-header">Daftar Dokumen</div>
          <button onclick="openPengesahan('SP3BP', this)">
            <i class="ph ph-file-text"></i>
            <span>SP3BP</span>
          </button>
          <button onclick="openPengesahan('SPTJB', this)">
            <i class="ph ph-file-doc"></i>
            <span>SPTJB</span>
          </button>
          <button onclick="openPengesahan('LRKB', this)">
            <i class="ph ph-clipboard-text"></i>
            <span>LRKB</span>
          </button>
        </div>
      @endif

      @php
        $hasAnyMaster = auth()->user()->hasPermission('MASTER_VIEW') ||
          auth()->user()->hasPermission('MASTER_RUANGAN_VIEW') ||
          auth()->user()->hasPermission('MASTER_PERUSAHAAN_VIEW') ||
          auth()->user()->hasPermission('MASTER_MOU_VIEW') ||
          auth()->user()->hasPermission('KODE_REKENING_PENDAPATAN_VIEW') ||
          auth()->user()->hasPermission('KODE_REKENING_PENGELUARAN_VIEW') ||
          auth()->user()->hasPermission('KODE_REKENING_VIEW') ||
          auth()->user()->isAdmin();
      @endphp

      @if(auth()->check() && $hasAnyMaster)
        <div class="menu-divider">Master Data</div>
        <button id="btnMaster" onclick="toggleMaster(this)">
          <i class="ph ph-gear"></i>
          <span>Pengaturan</span>
          <i class="ph ph-caret-down dropdown-icon"></i>
        </button>

        <div class="submenu-child" id="submenuMaster">
          @if(auth()->user()->hasPermission('MASTER_RUANGAN_VIEW') || auth()->user()->hasPermission('MASTER_VIEW'))
            <button onclick="openRuangan(this)">
              <i class="ph ph-door"></i>
              <span>Ruangan</span>
            </button>
          @endif
          @if(auth()->user()->hasPermission('MASTER_PERUSAHAAN_VIEW') || auth()->user()->hasPermission('MASTER_VIEW'))
            <button onclick="openPerusahaanPage(this)">
              <i class="ph ph-buildings"></i>
              <span>Perusahaan</span>
            </button>
          @endif
          @if(auth()->user()->hasPermission('MASTER_MOU_VIEW') || auth()->user()->hasPermission('MASTER_VIEW'))
            <button onclick="openMouPage(this)">
              <i class="ph ph-file-text"></i>
              <span>MOU</span>
            </button>
          @endif
          @if(auth()->user()->hasPermission('MASTER_VIEW') || auth()->user()->isAdmin())
            <button onclick="openPenandaTangan(this)">
              <i class="ph ph-signature"></i>
              <span>Penanda Tangan</span>
            </button>
          @endif
          @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('USER_VIEW'))
            <button onclick="openUsers(this)">
              <i class="ph ph-users-three"></i>
              <span>Users</span>
            </button>
          @endif
          @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('ACTIVITY_LOG_VIEW'))
            <button onclick="openActivityLogs(this)">
              <i class="ph ph-fingerprint"></i>
              <span>Log Aktivitas</span>
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
  <div id="globalLoader" class="confirm-overlay" style="z-index: 9999; flex-direction: column;">
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
  @include('dashboard.partials.sp3bp-modal')
  @include('dashboard.partials.lrkb-modal')

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
  <script src="{{ asset('js/dashboard/logs.js') }}?v={{ filemtime(public_path('js/dashboard/logs.js')) }}"></script>

  <!-- Modal Konfirmasi Universal (UI Berbasis Aksi) -->
  <div id="modalConfirmAction" class="confirm-overlay">
    <div class="confirm-box" style="max-width: 400px; text-align: center; padding: 30px;">
      <div id="confirmActionIcon" style="font-size: 3.5rem; margin-bottom: 15px;"></div>
      <h3 id="confirmActionTitle" style="margin-bottom: 12px; font-size: 1.25rem; font-weight: 700; color: #0f172a;">
        Konfirmasi
      </h3>
      <p id="confirmActionMessage" style="color: #64748b; font-size: 14px; margin-bottom: 30px; line-height: 1.6;">
      </p>
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

</body>

</html>