<div class="laporan">
    <div class="laporan-header">
        <div class="header-left">
            <h2 style="display: flex; align-items: center; gap: 10px;">
                <img src="https://lh3.googleusercontent.com/d/1L_r51MzZ9qlSFW1WKVvJM40DKtrA-6hx=w200"
                    style="height: 36px; width: auto; object-fit: contain;" alt="Logo Prov Kepri">
                Rencana Bisnis Anggaran (RBA) BLUD
            </h2>
            <p>Ringkasan Eksekutif Rencana Bisnis Anggaran Tahunan</p>
        </div>
        <div class="header-right">
            <div class="laporan-filter-group">
                <div class="filter-item">
                    <label>Tahun Anggaran</label>
                    <select id="laporanTahun" class="filter-date-input">
                        @php $curr = session('tahun_anggaran', date('Y')); @endphp
                        @for($y = $curr - 1; $y <= $curr + 1; $y++)
                            <option value="{{ $y }}" {{ $y == $curr ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <button class="btn-filter" onclick="loadLaporan('RBA')">
                    <i class="ph ph-magnifying-glass"></i>
                    <span>Tampilkan</span>
                </button>

                @if(auth()->user()->hasPermission('LAP_LRA_EXPORT') || auth()->user()->isAdmin())
                    <div class="filter-divider"></div>
                    <button class="btn-preview" onclick="openPreviewModal('RBA')">
                        <i class="ph ph-file-search"></i>
                        <span>Preview & Unduh</span>
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div id="rbaContent">
        <div style="text-align: center; padding: 100px 0; color: #94a3b8;">
            <i class="ph ph-briefcase" style="font-size: 48pt; opacity: 0.2; margin-bottom: 16px; display: block;"></i>
            <p>Silakan pilih tahun anggaran dan klik Tampilkan</p>
        </div>
    </div>
</div>







