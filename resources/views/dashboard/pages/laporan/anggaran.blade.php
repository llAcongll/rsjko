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
                    <input type="date" id="startDate" class="filter-date-input">
                </div>

                <div class="filter-item">
                    <label>Sampai Tanggal</label>
                    <input type="date" id="endDate" class="filter-date-input">
                </div>

                <div class="filter-item">
                    <label>Tahun</label>
                    <select id="laporanTahun" class="filter-date-input">
                        @php $curr = session('tahun_anggaran', date('Y')); @endphp
                        @for($y = $curr - 1; $y <= $curr + 1; $y++)
                            <option value="{{ $y }}" {{ $y == $curr ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
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
                    <select id="lraLevel" class="filter-date-input" style="width: 140px;">
                        <option value="1">Akun</option>
                        <option value="2">Kelompok</option>
                        <option value="3" selected>Jenis</option>
                        <option value="4">Objek</option>
                        <option value="5">Rincian Objek</option>
                        <option value="6">Sub Rincian Objek</option>
                    </select>
                </div>

                <button class="btn-filter" onclick="loadLaporan('ANGGARAN')">
                    <i class="ph ph-magnifying-glass"></i>
                    <span>Tampilkan</span>
                </button>

                @if(auth()->user()->hasPermission('LAP_RKA_VIEW'))
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
        <div style="padding: 60px 0; text-align: center; color: #94a3b8;">
            <i class="ph ph-calendar-check"
                style="font-size: 40px; display: block; margin-bottom: 10px; opacity: 0.3;"></i>
            <p>Klik tombol 'Tampilkan' untuk melihat rincian realisasi</p>
        </div>
    </div>
</div>






