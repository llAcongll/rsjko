<div class="laporan">
    <div class="laporan-header">
        <div class="header-left">
            <h2 style="display: flex; align-items: center; gap: 10px;">
                <img src="https://lh3.googleusercontent.com/d/1L_r51MzZ9qlSFW1WKVvJM40DKtrA-6hx=w200"
                    style="height: 36px; width: auto; object-fit: contain;" alt="Logo Prov Kepri">
                Laporan Pendapatan
            </h2>
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
                    <span>Tampilkan</span>
                </button>
                <div class="filter-divider" style="width: 1px; height: 24px; background: #e2e8f0; margin: 0 8px;"></div>
                @if(auth()->user()->hasPermission('LAPORAN_PENDAPATAN') || auth()->user()->hasPermission('LAPORAN_VIEW'))
                    <button class="btn-preview" onclick="openPreviewModal('PENDAPATAN')">
                        <i class="ph ph-eye"></i>
                        <span>Preview & Unduh</span>
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
        <!-- JASA Section -->
        <div class="laporan-section table-section">
            <div class="section-header">
                <h3><i class="ph ph-stethoscope"></i> Metode Jasa (Jasa Rumah Sakit & Jasa Pelayanan)</h3>
            </div>
            <div class="table-responsive">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 200px;">Sub Kode Rekening</th>
                            <th class="text-center">Nama Akun</th>
                            <th class="text-center" style="text-align:right">Jasa Rumah Sakit</th>
                            <th class="text-center" style="text-align:right">Jasa Pelayanan</th>
                            <th class="text-center" style="text-align:right">Total</th>
                        </tr>
                    </thead>
                    <tbody id="laporanJasaDetailedBody">
                        <!-- Dynamic -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- PAYMENT METHOD Section -->
        <div class="laporan-section table-section">
            <div class="section-header">
                <h3><i class="ph ph-credit-card"></i> Metode Pembayaran (Tunai & Non-Tunai)</h3>
            </div>
            <div class="table-responsive">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 200px;">Sub Kode Rekening</th>
                            <th class="text-center">Nama Akun</th>
                            <th class="text-center" style="text-align:right">Tunai</th>
                            <th class="text-center" style="text-align:right">Non-Tunai</th>
                            <th class="text-center" style="text-align:right">Total</th>
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
                            <th class="text-center" style="width: 200px;">Sub Kode Rekening</th>
                            <th class="text-center">Nama Akun</th>
                            <th class="text-center" style="text-align:right">BRK (Tunai + Transfer)</th>
                            <th class="text-center" style="text-align:right">BSI (Transfer)</th>
                            <th class="text-center" style="text-align:right">Total</th>
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