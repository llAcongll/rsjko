<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>

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

  <script>
    window.userRole = "{{ auth()->user()->role }}";
    window.userPermissions = {!! json_encode(auth()->user()->permissions ?? []) !!};
    window.isAdmin = {{ auth()->user()->isAdmin() ? 'true' : 'false' }};

    window.hasPermission = function (p) {
      if (window.isAdmin) return true;
      return window.userPermissions.includes(p);
    };
  </script>
</head>

<body>

  {{-- MOBILE HEADER --}}
  <div class="mobile-header">
    <div class="mobile-logo">RSJKO EHD</div>
    <button class="menu-toggle" onclick="toggleSidebar()">
      <i class="ph ph-list"></i>
    </button>
  </div>

  <div class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <div class="logo-wrapper">
        <span>RSJKO EHD</span>
      </div>
    </div>

    <div class="sidebar-content">
      <button onclick="openDashboard(this)">
        <i class="ph ph-chart-pie-slice"></i>
        <span>Dashboard</span>
      </button>

      @if(auth()->user()->hasPermission('REKENING_VIEW'))
        <button onclick="openRekening(this)">
          <i class="ph ph-notebook"></i>
          <span>Rekening Koran</span>
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

      @php
        $hasAnyPendapatan = auth()->user()->hasPermission('PENDAPATAN_UMUM_VIEW') ||
          auth()->user()->hasPermission('PENDAPATAN_BPJS_VIEW') ||
          auth()->user()->hasPermission('PENDAPATAN_JAMINAN_VIEW') ||
          auth()->user()->hasPermission('PENDAPATAN_KERJA_VIEW') ||
          auth()->user()->hasPermission('PENDAPATAN_LAIN_VIEW');
      @endphp

      @if($hasAnyPendapatan)
        <button id="btnPendapatan" onclick="togglePendapatan(this)">
          <i class="ph ph-coins"></i>
          <span>Pendapatan</span>
          <i class="ph ph-caret-down dropdown-icon"></i>
        </button>

        <div class="submenu-child" id="submenuPendapatan">
          @if(auth()->user()->hasPermission('PENDAPATAN_UMUM_VIEW'))
            <button onclick="openPendapatan('UMUM', this)">Pasien Umum</button>
          @endif
          @if(auth()->user()->hasPermission('PENDAPATAN_BPJS_VIEW'))
            <button onclick="openPendapatan('BPJS', this)">BPJS</button>
          @endif
          @if(auth()->user()->hasPermission('PENDAPATAN_JAMINAN_VIEW'))
            <button onclick="openPendapatan('JAMINAN', this)">Jaminan</button>
          @endif
          @if(auth()->user()->hasPermission('PENDAPATAN_KERJA_VIEW'))
            <button onclick="openPendapatan('KERJASAMA', this)">Kerjasama</button>
          @endif
          @if(auth()->user()->hasPermission('PENDAPATAN_LAIN_VIEW'))
            <button onclick="openPendapatan('LAIN', this)">Lain-lain</button>
          @endif
        </div>
      @endif

      @php
        $hasAnyLaporan = auth()->user()->hasPermission('LAPORAN_PENDAPATAN') ||
          auth()->user()->hasPermission('LAPORAN_REKON') ||
          auth()->user()->hasPermission('LAPORAN_PIUTANG') ||
          auth()->user()->hasPermission('LAPORAN_MOU') ||
          auth()->user()->hasPermission('LAPORAN_ANGGARAN');
      @endphp

      @if($hasAnyLaporan)
        <button id="btnLaporan" onclick="toggleLaporan(this)">
          <i class="ph ph-chart-bar"></i>
          <span>Laporan</span>
          <i class="ph ph-caret-down dropdown-icon"></i>
        </button>

        <div class="submenu-child" id="submenuLaporan">
          @if(auth()->user()->hasPermission('LAPORAN_PENDAPATAN'))
            <button onclick="openLaporan('PENDAPATAN', this)">Laporan Pendapatan</button>
          @endif
          @if(auth()->user()->hasPermission('LAPORAN_REKON'))
            <button onclick="openLaporan('REKON', this)">Laporan Rekonsiliasi</button>
          @endif
          @if(auth()->user()->hasPermission('LAPORAN_PIUTANG'))
            <button onclick="openLaporan('PIUTANG', this)">Laporan Piutang</button>
          @endif
          @if(auth()->user()->hasPermission('LAPORAN_MOU'))
            <button onclick="openLaporan('MOU', this)">Laporan MOU</button>
          @endif
          @if(auth()->user()->hasPermission('LAPORAN_ANGGARAN'))
            <button onclick="openLaporan('ANGGARAN', this)">Realisasi Anggaran</button>
          @endif
        </div>
      @endif

      @php
        $hasAnyMaster = auth()->user()->hasPermission('MASTER_VIEW') ||
          auth()->user()->hasPermission('KODE_REKENING_VIEW') ||
          auth()->user()->isAdmin();
      @endphp

      @if(auth()->check() && $hasAnyMaster)
        <div class="menu-divider">Master Data</div>
        <button id="btnMaster" onclick="toggleMaster(this)">
          <i class="ph ph-folder-notched"></i>
          <span>Pengaturan</span>
          <i class="ph ph-caret-down dropdown-icon"></i>
        </button>

        <div class="submenu-child" id="submenuMaster">
          @if(auth()->user()->hasPermission('KODE_REKENING_VIEW'))
            <button onclick="openKodeRekening(this)">Kode Rekening</button>
            <button onclick="openAnggaranRekening(this)">Anggaran</button>
          @endif
          @if(auth()->user()->hasPermission('MASTER_VIEW'))
            <button onclick="openRuangan(this)">Ruangan</button>
            <button onclick="openPerusahaanPage(this)">Perusahaan</button>
            <button onclick="openMouPage(this)">MOU</button>
          @endif
          @if(auth()->user()->isAdmin())
            <button onclick="openUsers(this)">Users</button>
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

      <form id="logoutForm" method="POST" action="/logout">
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

  {{-- MODAL DELETE --}}
  @include('dashboard.partials.confirm-delete')

  {{-- MODAL PREVIEW --}}
  @include('dashboard.partials.preview')

  {{-- MODAL USER --}}
  @include('dashboard.partials.user-form')

  {{-- MODAL RUANGAN --}}
  @include('dashboard.partials.ruangan-form')

  {{-- MODAL PERUSAHAAN --}}
  @include('dashboard.partials.perusahaan-form')

  {{-- MODAL MOU --}}
  @include('dashboard.partials.mou-form')

  {{-- MODAL REKENING --}}
  @include('dashboard.partials.rekening-form')

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
    src="{{ asset('js/dashboard/anggaran-rekening.js') }}?v={{ filemtime(public_path('js/dashboard/anggaran-rekening.js')) }}"></script>

</body>

</html>