<div class="laporan">
    <div class="laporan-header">
        <div class="header-left">
            <h2 style="display: flex; align-items: center; gap: 10px;">
                <img src="https://lh3.googleusercontent.com/d/1L_r51MzZ9qlSFW1WKVvJM40DKtrA-6hx=w200"
                    style="height: 36px; width: auto; object-fit: contain;" alt="Logo Prov Kepri">
                Laporan Realisasi Anggaran
            </h2>
            <p id="lraDescription">Perbandingan Pencapaian Pendapatan terhadap Target Anggaran Pendapatan</p>
        </div>
        <div class="header-right">
            <div class="laporan-filter-group">
                <div class="filter-item">
                    <label>Dari Tanggal</label>
                    <input type="date" id="laporanStart" class="filter-date-input">
                </div>
                <div class="filter-item">
                    <label>Sampai Tanggal</label>
                    <input type="date" id="laporanEnd" class="filter-date-input">
                </div>
                <div class="filter-item">
                    <label>Kategori</label>
                    <select id="lraCategory" class="filter-date-input" style="width: 140px;">
                        <option value="SEMUA" selected>SEMUA</option>
                        <option value="PENDAPATAN">PENDAPATAN</option>
                        <option value="PENGELUARAN">PENGELUARAN</option>
                    </select>
                </div>
                <div class="filter-item">
                    <label>Klasifikasi</label>
                    <select id="lraLevel" class="filter-date-input" style="width: 160px;">
                        <option value="" selected disabled>-- Pilih Klasifikasi --</option>
                        <option value="1">Akun</option>
                        <option value="2">Kelompok</option>
                        <option value="3">Jenis</option>
                        <option value="4">Objek</option>
                        <option value="5">Rincian Objek</option>
                        <option value="6">Sub Rincian Objek</option>
                    </select>
                </div>
                <button class="btn-filter" onclick="loadLaporan('ANGGARAN')">
                    <i class="ph ph-magnifying-glass"></i>
                    <span>Tampilkan</span>
                </button>
                @if(auth()->user()->hasPermission('LAPORAN_ANGGARAN') || auth()->user()->hasPermission('LAPORAN_VIEW'))
                    <button class="btn-preview" onclick="openPreviewModal('ANGGARAN')">
                        <i class="ph ph-file-search"></i>
                        <span>Preview & Unduh</span>
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- SUMMARY CARDS -->
    <div id="lraCardsContainer">
        <!-- Dynamic Cards -->
    </div>

    <div id="lraTableContainer">
        <div class="text-center py-5 text-slate-400" style="background:#f8fafc; border-radius:8px; border:2px dashed #e2e8f0; margin-top:20px;">
            Silakan pilih Klasifikasi terlebih dahulu untuk menampilkan rincian realisasi.
        </div>
    </div>
</div>