<div class="dashboard">

  {{-- DASHBOARD HEADER --}}
  <div class="dashboard-header">
    <div class="dashboard-header-left">
      <h2>Ringkasan Eksekutif</h2>
      <p>Data aktivitas dan pendapatan terakumulasi secara real-time</p>
    </div>

    <div class="dashboard-header-right">
      <span class="date">
        <i class="ph ph-calendar-blank"></i>
        {{ now()->translatedFormat('l, d F Y') }}
      </span>
    </div>
  </div>

  {{-- SUMMARY CARDS --}}
  <div class="dashboard-cards">

    <div class="dash-card blue">
      <div class="dash-card-icon">
        <i class="ph ph-trend-up"></i>
      </div>
      <div class="dash-card-content">
        <span class="label">Pendapatan Hari Ini</span>
        <h3 data-key="todayIncome">...</h3>
        <small data-key="todayGrowth">Menunggu data...</small>
      </div>
    </div>

    <div class="dash-card green">
      <div class="dash-card-icon">
        <i class="ph ph-chart-line-up"></i>
      </div>
      <div class="dash-card-content">
        <span class="label">Bulan Berjalan</span>
        <h3 data-key="monthIncome">...</h3>
        <small class="growth-up"><i class="ph ph-caret-up"></i> Terpantau Stabil</small>
      </div>
    </div>

    <div class="dash-card purple">
      <div class="dash-card-icon">
        <i class="ph ph-receipt"></i>
      </div>
      <div class="dash-card-content">
        <span class="label">Total Transaksi</span>
        <h3 data-key="todayTransaction">...</h3>
        <small>Hari ini</small>
      </div>
    </div>

    <div class="dash-card orange">
      <div class="dash-card-icon">
        <i class="ph ph-door"></i>
      </div>
      <div class="dash-card-content">
        <span class="label">Ruangan Aktif</span>
        <h3 data-key="activeRoom">...</h3>
        <small>Unit Pelayanan</small>
      </div>
    </div>

  </div>

  {{-- MAIN CONTENT --}}
  <div class="dashboard-main">

    {{-- GRAFIK --}}
    <div class="dashboard-box">
      <div class="box-header">
        <h4><i class="ph ph-chart-bar"></i> Tren Pendapatan Bulanan</h4>
      </div>

      <div class="chart-container" style="position: relative; height:320px;">
        <canvas id="incomeChart"></canvas>
      </div>
    </div>

    {{-- SIDE INFO --}}
    <div class="dashboard-side">

      {{-- DISTRIBUSI PASIEN --}}
      <div class="dashboard-box mb-4">
        <div class="box-header">
          <h4><i class="ph ph-users-three"></i> Jenis Pasien</h4>
        </div>

        <ul class="stat-list" id="distributionList">
          <li style="justify-content:center;color:#94a3b8;padding:20px 0;">Memuat data...</li>
        </ul>
      </div>

      {{-- RINGKASAN HARI INI --}}
      <div class="dashboard-box">
        <div class="box-header">
          <h4><i class="ph ph-info"></i> Info Harian</h4>
        </div>

        <ul class="stat-list" id="todaySummaryList">
          <li style="justify-content:center;color:#94a3b8;padding:20px 0;">Memuat data...</li>
        </ul>
      </div>

    </div>
  </div>

</div>