<div class="laporan">
    <div class="laporan-header">
        <div class="header-left">
            <h2 style="display: flex; align-items: center; gap: 10px;">
                <img src="https://lh3.googleusercontent.com/d/1L_r51MzZ9qlSFW1WKVvJM40DKtrA-6hx=w200"
                    style="height: 36px; width: auto; object-fit: contain;" alt="Logo Prov Kepri">
                Laporan Rekonsiliasi
            </h2>
            <p>Perbandingan data Rekening Koran (Bank) vs Modul Pendapatan</p>
        </div>
        <div class="header-right">
            <div class="laporan-filter-group">
                <div class="filter-item"
                    style="padding: 10px 16px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; font-weight: 600; color: #475569;">
                    Tahun Anggaran: {{ session('tahun_anggaran') }}
                </div>
                <button class="btn-filter" onclick="loadLaporan('REKON')">
                    <i class="ph ph-magnifying-glass"></i>
                    <span>Tampilkan</span>
                </button>
                @if(auth()->user()->hasPermission('LAPORAN_REKON') || auth()->user()->hasPermission('LAPORAN_VIEW'))
                    <button class="btn-preview" onclick="openPreviewModal('REKON')">
                        <i class="ph ph-file-search"></i>
                        <span>Preview & Unduh</span>
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- RECONCILIATION SUMMARY -->
    <style>
        /* Tighten page layout */
        .laporan {
            display: flex;
            flex-direction: column;
            gap: 16px !important;
        }

        .rekon-summary-container {
            display: flex;
            justify-content: center;
            margin-bottom: 8px;
        }

        .rekon-summary-container .dashboard-cards {
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            width: 100%;
            max-width: 850px;
            gap: 16px;
        }
    </style>

    <div class="rekon-summary-container">
        <div class="dashboard-cards">
            <div class="dash-card blue">
                <div class="dash-card-icon">
                    <i class="ph ph-bank"></i>
                </div>
                <div class="dash-card-content">
                    <span class="label">Total Bank</span>
                    <h3 id="rekonTotalBank">Rp 0</h3>
                    <small>Histori Rekening Koran</small>
                </div>
            </div>

            <div class="dash-card purple">
                <div class="dash-card-icon">
                    <i class="ph ph-hand-coins"></i>
                </div>
                <div class="dash-card-content">
                    <span class="label">Total Pendapatan</span>
                    <h3 id="rekonTotalPend">Rp 0</h3>
                    <small>Berdasarkan Modul</small>
                </div>
            </div>

            <div class="dash-card orange">
                <div class="dash-card-icon">
                    <i class="ph ph-warning"></i>
                </div>
                <div class="dash-card-content">
                    <span class="label">Net Selisih</span>
                    <h3 id="rekonTotalDiff">Rp 0</h3>
                    <small>Selisih Akumulasi</small>
                </div>
            </div>
        </div>
    </div>

    <!-- RECONCILIATION SECTION -->
    <div class="laporan-section rekon-section">
        <div class="table-responsive">
            <table class="report-table" id="laporanRekonTable">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 120px;">Bulan</th>
                        <th class="text-center" style="text-align:right">Bank (Kredit)</th>
                        <th class="text-center" style="text-align:right">Modul Netto</th>
                        <th class="text-center" style="text-align:right">Selisih Harian</th>
                        <th class="text-center" style="text-align:right">Selisih Kumulatif</th>
                        <th style="text-align:center; width: 120px;">Status</th>
                    </tr>
                </thead>
                <tbody id="laporanRekonBody">
                    <tr>
                        <td colspan="6" style="text-align:center">⏳ Memuat data rekon...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>