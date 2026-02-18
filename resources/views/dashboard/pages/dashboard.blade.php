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
    <!-- PENDAPATAN RUMAH SAKIT TAHUN ANGGARAN -->
    <div class="dash-card blue">
      <div class="dash-card-icon"><i class="ph ph-bank"></i></div>
      <div class="dash-card-content">
        <span class="label">Pendapatan RS (Tahun {{ session('tahun_anggaran') }})</span>
        <h3 data-key="totalPendapatanRS">Rp 0</h3>
        <small>Total Pendapatan Rumah Sakit</small>
      </div>
    </div>

    <!-- PENDAPATAN JASA PELAYANAN -->
    <div class="dash-card green">
      <div class="dash-card-icon"><i class="ph ph-stethoscope"></i></div>
      <div class="dash-card-content">
        <span class="label">Pendapatan Jasa Pelayanan</span>
        <h3 data-key="totalJasaPelayanan">Rp 0</h3>
        <small>Total Jasa Pelayanan Medis</small>
      </div>
    </div>

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
        <span class="label">Realisasi & Capaian</span>
        <div style="display: flex; align-items: baseline; gap: 8px;">
          <h3 data-key="realisasiPendapatan">Rp 0</h3>
          <span data-key="persenCapaian" style="font-size: 14px; font-weight: bold; color: #9333ea;">(0%)</span>
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
        <h4><i class="ph ph-chart-bar"></i> Tren Pendapatan Bulanan Tahun {{ session('tahun_anggaran') }}</h4>
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
    <div class="dashboard-box">
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

  </div>
</div>