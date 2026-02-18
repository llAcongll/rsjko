<div class="laporan">
    <div class="laporan-header">
        <div class="header-left">
            <h2><i class="ph ph-chart-bar"></i> Laporan Pendapatan</h2>
            <p>Rekapitulasi pendapatan dan statistik pasien berdasarkan periode</p>
        </div>
        <div class="header-right">
            <div class="laporan-filter-group">
                <div class="filter-item">
                    <label>Dari</label>
                    <input type="date" id="laporanStart" class="filter-date-input">
                </div>
                <div class="filter-item">
                    <label>Sampai</label>
                    <input type="date" id="laporanEnd" class="filter-date-input">
                </div>
                <button class="btn-filter" onclick="loadLaporan('PENDAPATAN')">
                    <i class="ph ph-magnifying-glass"></i>
                    <span>Filter</span>
                </button>
                @if(auth()->user()->hasPermission('LAPORAN_EXPORT'))
                    <button class="btn-filter" style="background: #10b981; border-color: #10b981" onclick="exportLaporan()">
                        <i class="ph ph-file-xls"></i>
                        <span>Excel</span>
                    </button>
                    <button class="btn-filter" style="background: #ef4444; border-color: #ef4444" onclick="exportPdf()">
                        <i class="ph ph-file-pdf"></i>
                        <span>PDF</span>
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Row 1: Income Type Cards -->
    <div class="laporan-main-cards" id="laporanTypeCards">
        <!-- Dynamic Cards: Umum, BPJS, Jaminan, Kerjasama, Lain-lain -->
    </div>

    <div class="laporan-grid" style="grid-template-columns: 1fr; gap: 24px;">
        <!-- PAYMENT METHOD Section -->
        <div class="laporan-section table-section">
            <div class="section-header">
                <h3><i class="ph ph-credit-card"></i> Metode Pembayaran (Tunai & Non-Tunai)</h3>
            </div>
            <div class="table-responsive">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th style="width: 200px;">Sub Kode Rekening</th>
                            <th>Nama Akun</th>
                            <th style="text-align:right">Tunai</th>
                            <th style="text-align:right">Non-Tunai</th>
                            <th style="text-align:right">Total</th>
                        </tr>
                    </thead>
                    <tbody id="laporanPaymentDetailedBody">
                        <!-- Dynamic -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- BANK RECEPTION Section -->
        <div class="laporan-section table-section">
            <div class="section-header">
                <h3><i class="ph ph-bank"></i> Penerimaan Per Bank</h3>
            </div>
            <div class="table-responsive">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th style="width: 200px;">Sub Kode Rekening</th>
                            <th>Nama Akun</th>
                            <th style="text-align:right">BRK (Tunai + Transfer)</th>
                            <th style="text-align:right">BSI (Transfer)</th>
                            <th style="text-align:right">Total</th>
                        </tr>
                    </thead>
                    <tbody id="laporanBankDetailedBody">
                        <!-- Dynamic -->
                    </tbody>
                </table>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
            <!-- TOP ROOMS -->
            <div class="laporan-section">
                <div class="section-header">
                    <h3>Pendapatan Per Ruangan</h3>
                </div>
                <div class="room-list" id="laporanRoomBody">
                    <!-- Dynamic -->
                </div>
            </div>

            <!-- PATIENT STATS -->
            <div class="laporan-section">
                <div class="section-header">
                    <h3>Statistik Pasien Per Ruangan</h3>
                </div>
                <div class="patient-stats" id="laporanPatientBody">
                    <!-- Dynamic -->
                </div>
            </div>
        </div>
    </div>
</div>