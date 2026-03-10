<div class="laporan">
    <div class="laporan-header">
        <div class="header-left">
            <h2 style="display: flex; align-items: center; gap: 10px;">
                <img src="https://lh3.googleusercontent.com/d/1L_r51MzZ9qlSFW1WKVvJM40DKtrA-6hx=w200"
                    style="height: 36px; width: auto; object-fit: contain;" alt="Logo Prov Kepri">
                Neraca (Balance Sheet)
            </h2>
            <p>Laporan Posisi Keuangan Berdasarkan SAP Akrual</p>
        </div>
        <div class="header-right">
            <div class="laporan-filter-group">
                <div class="filter-item">
                    <label>Per Akhir Bulan</label>
                    <select id="neracaFilterBulan" class="filter-date-input">
                        @foreach(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $idx => $m)
                            <option value="{{ $idx + 1 }}" {{ date('n') == $idx + 1 ? 'selected' : '' }}>{{ $m }}</option>
                        @endforeach
                    </select>
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

                <button class="btn-filter" onclick="loadLaporan('NERACA')">
                    <i class="ph ph-magnifying-glass"></i>
                    <span>Tampilkan</span>
                </button>

                @if(auth()->user()->hasPermission('LAP_NERACA_VIEW'))
                    <div class="filter-divider"></div>
                    <button class="btn-preview" onclick="openPreviewModal('NERACA')">
                        <i class="ph ph-file-search"></i>
                        <span>Preview & Unduh</span>
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div id="neracaContent" class="laporan-body" style="margin-top: 20px;">
        <div class="empty-state"
            style="text-align: center; padding: 50px; background: #fff; border-radius: 12px; border: 1px dashed #cbd5e1;">
            <i class="ph ph-files" style="font-size: 48px; color: #94a3b8;"></i>
            <p style="margin-top: 10px; color: #64748b;">Silakan pilih bulan dan klik Tampilkan</p>
        </div>
    </div>
</div>






