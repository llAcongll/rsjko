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
                @if(auth()->user()->hasPermission('LAP_PENDAPATAN_VIEW') || auth()->user()->hasPermission('LAP_PENDAPATAN_EXPORT') || auth()->user()->hasPermission('LAP_LRA_VIEW'))
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
                <div class="table-container">
                    <table class="report-table universal-table">
                        <thead>
                            <tr>
                                <th class="text-center">Sub Kode Rekening</th>
                                <th class="text-center">Nama Akun</th>
                                <th class="text-center">Jasa Rumah Sakit</th>
                                <th class="text-center">Jasa Pelayanan</th>
                                <th class="text-center">Total</th>
                            </tr>
                        </thead>
                        <tbody id="laporanJasaDetailedBody">
                            <!-- Dynamic -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- PAYMENT METHOD Section -->
        <div class="laporan-section table-section">
            <div class="section-header">
                <h3><i class="ph ph-credit-card"></i> Metode Pembayaran (Tunai & Non-Tunai)</h3>
            </div>
            <div class="table-responsive">
                <div class="table-container">
                    <table class="report-table universal-table">
                        <thead>
                            <tr>
                                <th class="text-center">Sub Kode Rekening</th>
                                <th class="text-center">Nama Akun</th>
                                <th class="text-center">Tunai</th>
                                <th class="text-center">Non-Tunai</th>
                                <th class="text-center">Total</th>
                            </tr>
                        </thead>
                        <tbody id="laporanPaymentDetailedBody">
                            <!-- Dynamic -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- BANK RECEPTION Section -->
        <div class="laporan-section table-section">
            <div class="section-header">
                <h3><i class="ph ph-bank"></i> Penerimaan Per Bank</h3>
            </div>
            <div class="table-responsive">
                <div class="table-container">
                    <table class="report-table universal-table">
                        <thead>
                            <tr>
                                <th class="text-center">Sub Kode Rekening</th>
                                <th class="text-center">Nama Akun</th>
                                <th class="text-center">BRK (Tunai + Transfer)</th>
                                <th class="text-center">BSI (Transfer)</th>
                                <th class="text-center">Total</th>
                            </tr>
                        </thead>
                        <tbody id="laporanBankDetailedBody">
                            <!-- Dynamic -->
                        </tbody>
                    </table>
                </div>
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

        <!-- ADDITIVE SECTIONS: 1. PENERIMAAN PASIEN TUNAI -->
        <div class="laporan-section table-section">
            <div class="section-header">
                <h3><i class="ph ph-wallet"></i> PENERIMAAN PASIEN TUNAI</h3>
            </div>
            <div class="table-responsive">
                <table class="report-table universal-table">
                    <thead>
                        <tr style="background: #fbbf24;">
                            <th class="text-center">NO</th>
                            <th class="text-center">UNIT</th>
                            <th class="text-center">TOTAL PASIEN</th>
                            <th class="text-center">JUMLAH (RP)</th>
                        </tr>
                    </thead>
                    <tbody id="laporanTunaiBody"></tbody>
                </table>
            </div>
        </div>

        <!-- 2. PENERIMAAN PASIEN NON TUNAI -->
        <div class="laporan-section table-section">
            <div class="section-header">
                <h3><i class="ph ph-credit-card"></i> PENERIMAAN PASIEN NON TUNAI</h3>
            </div>
            <div class="table-responsive">
                <table class="report-table universal-table">
                    <thead>
                        <tr style="background: #fbbf24;">
                            <th class="text-center">NO</th>
                            <th class="text-center">UNIT</th>
                            <th class="text-center">PASIEN QRIS</th>
                            <th class="text-center">PASIEN TRANSFER</th>
                            <th class="text-center">TOTAL PASIEN</th>
                            <th class="text-center">QRIS (RP)</th>
                            <th class="text-center">TRANSFER (RP)</th>
                            <th class="text-center">TOTAL (RP)</th>
                        </tr>
                    </thead>
                    <tbody id="laporanNonTunaiBody"></tbody>
                </table>
            </div>
        </div>

        <!-- 3. PENERIMAAN PASIEN BPJS KESEHATAN -->
        <div class="laporan-section table-section">
            <div class="section-header">
                <h3><i class="ph ph-shield-check"></i> PENERIMAAN PASIEN BPJS KESEHATAN</h3>
            </div>
            <div class="table-responsive">
                <table class="report-table universal-table">
                    <thead>
                        <tr style="background: #fbbf24;">
                            <th class="text-center">NO</th>
                            <th class="text-center">UNIT</th>
                            <th class="text-center">TOTAL PASIEN</th>
                            <th class="text-center">BPJS (GROSS)</th>
                            <th class="text-center">VPK / POTONGAN</th>
                            <th class="text-center">ADM BANK</th>
                            <th class="text-center">JUMLAH (NET)</th>
                        </tr>
                    </thead>
                    <tbody id="laporanBpjsBody"></tbody>
                </table>
            </div>
        </div>

        <!-- 4. PENERIMAAN PASIEN JAMINAN -->
        <div class="laporan-section table-section">
            <div class="section-header">
                <h3><i class="ph ph-buildings"></i> PENERIMAAN PASIEN JAMINAN (ASURANSI, PT, DLL)</h3>
            </div>
            <div class="table-responsive">
                <table class="report-table universal-table">
                    <thead>
                        <tr style="background: #fbbf24;">
                            <th class="text-center">NO</th>
                            <th class="text-center">PENJAMIN / PERUSAHAAN</th>
                            <th class="text-center">UNIT</th>
                            <th class="text-center">TOTAL PASIEN</th>
                            <th class="text-center">JUMLAH (RP)</th>
                        </tr>
                    </thead>
                    <tbody id="laporanJaminanBody"></tbody>
                </table>
            </div>
        </div>

        <!-- 5. PENERIMAAN KERJA SAMA -->
        <div class="laporan-section table-section">
            <div class="section-header">
                <h3><i class="ph ph-handshake"></i> PENERIMAAN KERJA SAMA (PKL, MAGANG, PENELITIAN, DLL)</h3>
            </div>
            <div class="table-responsive">
                <table class="report-table universal-table">
                    <thead>
                        <tr style="background: #fbbf24;">
                            <th class="text-center">NO</th>
                            <th class="text-center">KERJA SAMA (INSTANSI)</th>
                            <th class="text-center">JUMLAH KEGIATAN</th>
                            <th class="text-center">TOTAL PENDAPATAN</th>
                        </tr>
                    </thead>
                    <tbody id="laporanKerjasamaBody"></tbody>
                </table>
            </div>
        </div>

        <!-- 6. PENERIMAAN LAIN-LAIN -->
        <div class="laporan-section table-section">
            <div class="section-header">
                <h3><i class="ph ph-dots-three-circle"></i> PENERIMAAN LAIN-LAIN</h3>
            </div>
            <div class="table-responsive">
                <table class="report-table universal-table">
                    <thead>
                        <tr style="background: #fbbf24;">
                            <th class="text-center">NO</th>
                            <th class="text-center">KETERANGAN</th>
                            <th class="text-center">JUMLAH KEGIATAN</th>
                            <th class="text-center">TOTAL PENDAPATAN</th>
                        </tr>
                    </thead>
                    <tbody id="laporanLainBody"></tbody>
                </table>
            </div>
        </div>

        <!-- SUMMARY: PENERIMAAN PER BANK -->
        <div class="laporan-section table-section">
            <div class="section-header">
                <h3><i class="ph ph-bank"></i> PENERIMAAN PER BANK</h3>
            </div>
            <div class="table-responsive">
                <table class="report-table universal-table">
                    <thead>
                        <tr style="background: #fbbf24;">
                            <th class="text-center">NO</th>
                            <th class="text-center">NAMA BANK</th>
                            <th class="text-center">TOTAL TRANSAKSI</th>
                            <th class="text-center">JUMLAH (RP)</th>
                        </tr>
                    </thead>
                    <tbody id="laporanBankSummaryBody"></tbody>
                </table>
            </div>
        </div>

        <!-- SUMMARY: PENDAPATAN PER UNIT -->
        <div class="laporan-section table-section">
            <div class="section-header">
                <h3><i class="ph ph-list-numbers"></i> PENDAPATAN PER UNIT</h3>
            </div>
            <div class="table-responsive">
                <table class="report-table universal-table">
                    <thead>
                        <tr style="background: #fbbf24;">
                            <th class="text-center">NO</th>
                            <th class="text-center">UNIT</th>
                            <th class="text-center">TOTAL PASIEN</th>
                            <th class="text-center">JUMLAH (RP)</th>
                        </tr>
                    </thead>
                    <tbody id="laporanUnitSummaryBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>