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
  <div class="dashboard-cards anggaran-cards">
    <!-- TARGET PENDAPATAN -->
    <div class="dash-card orange">
      <div class="dash-card-icon"><i class="ph ph-target"></i></div>
      <div class="dash-card-content">
        <span class="label">Target Pendapatan</span>
        <h3 data-key="targetPendapatan">Rp 0</h3>
        <small>Target Tahun Anggaran {{ session('tahun_anggaran') }}</small>
      </div>
    </div>

    <!-- REALISASI & PERSENTASE -->
    <div class="dash-card purple">
      <div class="dash-card-icon"><i class="ph ph-chart-pie-slice"></i></div>
      <div class="dash-card-content">
        <span class="label">Realisasi & Capaian Pendapatan</span>
        <div style="display: flex; align-items: baseline; gap: 4px;">
          <h3 data-key="realisasiPendapatan">Rp 0</h3>
          <span data-key="persenCapaian" style="font-size: 12px; font-weight: bold; color: #9333ea;">(0%)</span>
        </div>
        <small>Realisasi s.d Hari Ini</small>
      </div>
    </div>

    <!-- TARGET PENGELUARAN -->
    <div class="dash-card red">
      <div class="dash-card-icon"><i class="ph ph-receipt"></i></div>
      <div class="dash-card-content">
        <span class="label">Target Pengeluaran</span>
        <h3 data-key="targetPengeluaran">Rp 0</h3>
        <small>Target Tahun Anggaran {{ session('tahun_anggaran') }}</small>
      </div>
    </div>

    <!-- REALISASI PENGELUARAN -->
    <div class="dash-card indigo">
      <div class="dash-card-icon"><i class="ph ph-trend-up"></i></div>
      <div class="dash-card-content">
        <span class="label">Realisasi Pengeluaran</span>
        <div style="display: flex; align-items: baseline; gap: 4px;">
          <h3 data-key="realisasiPengeluaran">Rp 0</h3>
          <span data-key="persenCapaianPengeluaran" style="font-size: 12px; font-weight: bold; color: #4f46e5;">(0%)
          </span>
        </div>
        <small>Realisasi s.d Hari Ini</small>
      </div>
    </div>
  </div>

  {{-- MAIN CONTENT --}}
  <div class="dashboard-main">

    {{-- GRAFIK --}}
    {{-- GRAFIK PENDAPATAN --}}
    <div class="dashboard-box mb-4">
      <div class="box-header">
        <h4><i class="ph ph-chart-bar"></i> Tren Keuangan Bulanan Tahun {{ session('tahun_anggaran') }}</h4>
      </div>

      <div class="chart-container" style="position: relative; height:320px;">
        <canvas id="incomeChart"></canvas>
      </div>
    </div>

    {{-- GRAFIK RUANGAN --}}
    <div class="dashboard-box mb-4">
      <div class="box-header">
        <h4><i class="ph ph-buildings"></i> Tren Pendapatan Seluruh Unit/Ruangan</h4>
      </div>

      <div class="chart-container" style="position: relative; height:400px;">
        <canvas id="roomChart"></canvas>
      </div>
    </div>

    {{-- GRAFIK PERSENTASE JENIS PASIEN --}}
    <div class="dashboard-box mb-4">
      <div class="box-header">
        <h4><i class="ph ph-chart-pie-slice"></i> Persentase Jenis Pasien</h4>
      </div>

      <div style="display: flex; gap: 20px; align-items: flex-start; flex-wrap: wrap;">
        <div
          style="flex: 1; min-width: 250px; position: relative; height: 300px; display: flex; justify-content: center;">
          <canvas id="patientPieChart"></canvas>
        </div>

        <div style="flex: 1; min-width: 250px;">
          <ul class="stat-list mt-2" id="distributionList">
            <li style="justify-content:center;color:#94a3b8;padding:20px 0;">Memuat data...</li>
          </ul>
        </div>
      </div>
    </div>

    {{-- GRAFIK PENGELUARAN KATEGORI --}}
    <div class="dashboard-box">
      <div class="box-header">
        <h4><i class="ph ph-hand-coins"></i> Tren Pengeluaran Seluruh Kategori</h4>
      </div>

      <div class="chart-container" style="position: relative; height:400px;">
        <canvas id="expenditureChart"></canvas>
      </div>
    </div>

  </div>
</div>