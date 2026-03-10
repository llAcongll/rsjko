<div class="page-container">
    {{-- HEADER --}}
    <div class="page-header">
        <div class="page-header-left">
            <h2><i class="ph ph-file-text"></i> Piutang Usaha</h2>
            <p>Kelola data piutang dan tagihan perusahaan/asuransi</p>
        </div>

        <div class="page-header-right">
            @if(auth()->user()->hasPermission('PIUTANG_MANAGE'))
                <button class="btn-tambah-data" id="btnTambahPiutang">
                    <i class="ph-bold ph-plus"></i>
                    <span>Catat Piutang</span>
                </button>
            @endif
        </div>
    </div>

    {{-- SUMMARY CARDS --}}
    <div class="pendapatan-summary-container">
        <div class="dashboard-cards" style="grid-template-columns: repeat(3, 1fr); max-width: 850px; width: 100%;">
            <div class="dash-card blue">
                <div class="dash-card-icon">
                    <i class="ph ph-file-text"></i>
                </div>
                <div class="dash-card-content">
                    <span class="label">Total Piutang Berjalan</span>
                    <h3 id="summaryTotalPiutang">Rp 0</h3>
                </div>
            </div>

            <div class="dash-card indigo">
                <div class="dash-card-icon">
                    <i class="ph ph-scissors"></i>
                </div>
                <div class="dash-card-content">
                    <span class="label">Potongan Tagihan</span>
                    <h3 id="summaryTotalPotongan">Rp 0</h3>
                </div>
            </div>

            <div class="dash-card orange">
                <div class="dash-card-icon">
                    <i class="ph ph-bank"></i>
                </div>
                <div class="dash-card-content">
                    <span class="label">Administrasi Bank</span>
                    <h3 id="summaryTotalAdm">Rp 0</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- MAIN CONTENT --}}
    <div class="dashboard-box">
        <div class="table-toolbar">
            <div class="table-search-wrapper">
                <i class="ph ph-magnifying-glass"></i>
                <input type="text" id="searchPiutang" class="table-search"
                    placeholder="Cari perusahaan, bulan, keterangan...">
            </div>
        </div>

        <div class="table-container">
            <table id="piutangTable" class="table universal-table">
                <thead>
                    <tr>
                        <th class="text-center checkbox-col">No</th>
                        <th class="text-center sortable">Tanggal</th>
                        <th class="text-center sortable">Perusahaan / Debitur</th>
                        <th class="text-center sortable">Bulan Pelayanan</th>
                        <th class="text-right sortable">Jumlah Tagihan</th>
                        <th class="text-center sortable">Status</th>
                        <th class="action-col">Aksi</th>
                    </tr>
                </thead>
                <tbody id="piutangBody">
                    <tr>
                        <td colspan="7" class="text-center">
                            <i class="ph ph-tray" style="font-size: 32px; margin-bottom: 8px;"></i>
                            <p>Memuat data piutang...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="flex justify-between items-center mt-4">
            <p id="paginationInfoPiutang" class="text-slate-500" style="font-size: 13px;">Menampilkan 0-0 dari 0 data
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

{{-- MODALS --}}
@include('dashboard.partials.piutang-form')
@include('dashboard.partials.piutang-detail')
</div>





