@php
    $title = 'SPTJB (Surat Pernyataan Tanggung Jawab Belanja)';
@endphp

<div class="laporan"> {{-- Menggunakan class laporan agar CSS-nya konsisten --}}
    <div class="laporan-header">
        <div class="header-left">
            <h2 style="display: flex; align-items: center; gap: 10px; line-height: 1.2;">
                <i class="ph ph-file-doc"></i>
                <div>
                    LAPORAN REALISASI PENDAPATAN, BELANJA DAN PEMBIAYAAN<br>
                    <small id="sptjbTitlePeriod" style="font-size: 14px; color: #64748b; font-weight: 500;">Pilih
                        Triwulan untuk menampilkan periode</small>
                </div>
            </h2>
        </div>
        <div class="header-right">
            <div class="laporan-filter-group">
                <div class="filter-item">
                    <label>Triwulan</label>
                    <select id="sptjbTriwulan" class="filter-date-input" style="width: 150px;"
                        onchange="handleTriwulanChange(this.value)">
                        <option value="">Pilih...</option>
                        <optgroup label="Triwulan">
                            <option value="1">Triwulan 1 (Jan - Mar)</option>
                            <option value="2">Triwulan 2 (Apr - Jun)</option>
                            <option value="3">Triwulan 3 (Jul - Sep)</option>
                            <option value="4">Triwulan 4 (Okt - Des)</option>
                        </optgroup>
                        <optgroup label="Bulanan">
                            <option value="m1">Januari</option>
                            <option value="m2">Februari</option>
                            <option value="m3">Maret</option>
                            <option value="m4">April</option>
                            <option value="m5">Mei</option>
                            <option value="m6">Juni</option>
                            <option value="m7">Juli</option>
                            <option value="m8">Agustus</option>
                            <option value="m9">September</option>
                            <option value="m10">Oktober</option>
                            <option value="m11">November</option>
                            <option value="m12">Desember</option>
                        </optgroup>
                    </select>
                </div>
                <input type="hidden" id="laporanStart">
                <input type="hidden" id="laporanEnd">
                <div class="filter-item">
                    <label>Kategori</label>
                    <select id="lraCategory" class="filter-date-input" style="width: 140px;">
                        <option value="SEMUA" selected>SEMUA</option>
                        <option value="PENDAPATAN">PENDAPATAN</option>
                        <option value="PENGELUARAN">PENGELUARAN</option>
                    </select>
                </div>
                <button class="btn-filter" id="btnSptjbLoad" onclick="performManualLoad()">
                    <i class="ph ph-magnifying-glass"></i>
                    <span>Tampilkan</span>
                </button>
                <button class="btn-preview" onclick="openPreviewModal('ANGGARAN')">
                    <i class="ph ph-file-search"></i>
                    <span>Preview & Unduh</span>
                </button>
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
            <p>Silakan pilih Triwulan untuk melihat data</p>
        </div>
    </div>
</div>

{{-- Script logic moved to laporan.js to handle AJAX dynamic loading correctly --}}