<div class="dashboard">

    {{-- HEADER --}}
    <div class="dashboard-header">
        <div class="dashboard-header-left">
            <h2><i class="ph ph-file-text"></i> Piutang Usaha</h2>
            <p>Kelola data piutang dan tagihan perusahaan/asuransi</p>
        </div>

        <div class="dashboard-header-right">
            @if(auth()->user()->hasPermission('PIUTANG_CRUD'))
                <button class="btn-tambah-data" id="btnTambahPiutang">
                    <i class="ph-bold ph-plus"></i>
                    <span>Catat Piutang</span>
                </button>
            @endif
        </div>
    </div>

    {{-- SUMMARY CARDS --}}
    <div class="dashboard-cards">
        <div class="dash-card blue">
            <div class="dash-card-icon">
                <i class="ph ph-money"></i>
            </div>
            <div class="dash-card-content">
                <span class="label">Total Piutang Berjalan</span>
                <h3 id="summaryTotalPiutang">Rp 0</h3>
                <small>Akumulasi tagihan</small>
            </div>
        </div>
    </div>

    {{-- MAIN CONTENT --}}
    <div class="dashboard-box">
        <div class="box-header">
            <div class="toolbar-row">
                <div class="search-wrapper">
                    <div class="input-group" style="position: relative;">
                        <i class="ph ph-magnifying-glass"
                            style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 18px;"></i>
                        <input type="text" id="searchPiutang" placeholder="Cari perusahaan, bulan, keterangan..."
                            style="width: 100%; height: 44px; padding-left: 48px; border-radius: 12px; border: 1px solid #e2e8f0; font-size: 14px;">
                    </div>
                </div>

                <div class="toolbar-actions">
                </div>
            </div>
        </div>

        <div class="table-container">
            <table id="piutangTable">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">No</th>
                        <th style="width: 110px;">Tanggal</th>
                        <th>Perusahaan / Debitur</th>
                        <th>Bulan Pelayanan</th>
                        <th class="text-right">Jumlah Tagihan</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" style="width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="piutangBody">
                    <tr>
                        <td colspan="7" class="text-center" style="padding: 40px; color: #94a3b8;">
                            <i class="ph ph-tray" style="font-size: 32px; margin-bottom: 8px;"></i>
                            <p>Memuat data piutang...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="flex justify-between items-center mt-2">
            <p id="paginationInfoPiutang" class="text-slate-500" style="font-size: 13px;">Menampilkan 0–0 dari 0 data
            </p>

            <div class="flex items-center gap-2">
                <button id="prevPagePiutang" class="btn-aksi" disabled><i class="ph ph-caret-left"></i></button>
                <span id="pageInfoPiutang" class="font-medium"
                    style="font-size: 14px; min-width: 100px; text-align: center;">1 / 1</span>
                <button id="nextPagePiutang" class="btn-aksi" disabled><i class="ph ph-caret-right"></i></button>
            </div>
        </div>
    </div>

</div>


</div>