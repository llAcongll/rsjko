<div class="page-container">
    <div id="masterListSectionBpjs">
        {{-- HEADER --}}
        <div class="page-header">
            <div class="page-header-left">
                <h2><i class="ph ph-folder-open"></i> Kelompok Pendapatan BPJS</h2>
                <p>Kelola data klaim BPJS berdasar kelompok/tanggal</p>
            </div>

            <div class="page-header-right">
                @if(auth()->user()->hasPermission('PENDAPATAN_BPJS_CREATE') || auth()->user()->hasPermission('PENDAPATAN_BPJS_CRUD') || auth()->user()->hasPermission('PENDAPATAN_BPJS_POST'))
                    <button class="btn-toolbar btn-toolbar-info" onclick="bulkPostMasterBpjs()">
                        <i class="ph ph-check-square-offset"></i>
                        <span>Posting Masal</span>
                    </button>
                    <button class="btn-toolbar btn-toolbar-outline" onclick="bulkUnpostMasterBpjs()"
                        style="border-color: #f59e0b; color: #d97706;">
                        <i class="ph ph-arrow-counter-clockwise"></i>
                        <span>Batal Posting Masal</span>
                    </button>
                    @if(auth()->user()->hasPermission('REVENUE_SYNC'))
                        <button class="btn-toolbar btn-toolbar-outline" onclick="syncOldData()"
                            style="border-color: #3b82f6; color: #2563eb;">
                            <i class="ph ph-arrows-counter-clockwise"></i>
                            <span>Sinkronisasi</span>
                        </button>
                    @endif
                    <button class="btn-tambah-data" onclick="openMasterFormBpjs()">
                        <i class="ph-bold ph-plus"></i>
                        <span>Kelompok Baru</span>
                    </button>
                @endif
            </div>
        </div>

        {{-- SUMMARY CARDS (MASTER) --}}
        <div class="grid-responsive grid-3 mb-4" style="max-width: 850px; margin-inline: auto;">
            <div class="dash-card blue">
                <div class="dash-card-icon"><i class="ph ph-hospital"></i></div>
                <div class="dash-card-content">
                    <span class="label">Jasa Rumah Sakit</span>
                    <h3 id="masterSummaryRsBpjs">Rp 0</h3>
                </div>
            </div>
            <div class="dash-card purple">
                <div class="dash-card-icon"><i class="ph ph-user-gear"></i></div>
                <div class="dash-card-content">
                    <span class="label">Jasa Pelayanan</span>
                    <h3 id="masterSummaryPelayananBpjs">Rp 0</h3>
                </div>
            </div>
            <div class="dash-card green">
                <div class="dash-card-icon"><i class="ph ph-bank"></i></div>
                <div class="dash-card-content">
                    <span class="label">Total Klaim BPJS</span>
                    <h3 id="masterSummaryTotalBpjs" style="color: #16a34a;">Rp 0</h3>
                </div>
            </div>
        </div>

        {{-- MAIN CONTENT (MASTER) --}}
        <div class="dashboard-box">
            <div class="table-toolbar">
                <div class="table-search-wrapper">
                    <i class="ph ph-magnifying-glass"></i>
                    <input type="text" id="searchMasterBpjs" class="table-search"
                        placeholder="Cari di daftar kelompok..." data-table="masterTable">
                </div>
                <div class="filter-wrapper">
                    <select id="filterStatusMasterBpjs" class="filter-select">
                        <option value="">Semua Status</option>
                        <option value="DRAFT">📑 Draft</option>
                        <option value="POSTED">✅ Diposting</option>
                    </select>
                </div>
                <div class="filter-wrapper">
                    <input type="month" id="filterMonthMasterBpjs" class="filter-select" style="max-width: 160px;">
                </div>
            </div>

            <div id="selectionBannerBpjs"
                style="display:none; background: #eff6ff; padding: 12px; border-radius: 8px; margin-bottom: 12px; border: 1px solid #bfdbfe; text-align: center; font-size: 13px; color: #1e40af;">
                Semua <span id="countCurrentPageBpjs">-</span> kelompok di halaman ini telah terpilih.
            </div>

            <div class="table-container">
                <table id="masterTable" class="universal-table table-mobile-cards">
                    <thead>
                        <tr>
                            <th class="checkbox-col">
                                <input type="checkbox" id="checkAllMasterBpjs" onclick="toggleAllMasterBpjs(this)" />
                            </th>
                            <th class="text-center sortable" onclick="sortMasterBpjs('tanggal')" data-sort="tanggal">
                                Tanggal PDPT/RK <i class="ph ph-caret-up-down"></i></th>
                            <th class="text-center sortable" onclick="sortMasterBpjs('keterangan')"
                                data-sort="keterangan">Keterangan / No. Bukti <i class="ph ph-caret-up-down"></i></th>
                            <th class="text-right sortable" onclick="sortMasterBpjs('total_rs')" data-sort="total_rs">
                                Jasa RS <i class="ph ph-caret-up-down"></i></th>
                            <th class="text-right sortable" onclick="sortMasterBpjs('total_pelayanan')"
                                data-sort="total_pelayanan">Jasa Pelayanan <i class="ph ph-caret-up-down"></i></th>
                            <th class="text-right sortable" onclick="sortMasterBpjs('total_all')" data-sort="total_all">
                                Total (Rp) <i class="ph ph-caret-up-down"></i></th>
                            <th class="text-center sortable" onclick="sortMasterBpjs('is_posted')"
                                data-sort="is_posted">Status <i class="ph ph-caret-up-down"></i></th>
                            <th class="action-col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="masterTableBodyBpjs">
                        <tr>
                            <td colspan="8" class="text-center">Memuat data...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="flex justify-between items-center mt-2">
                <p id="paginationInfoMasterBpjs" class="text-slate-500" style="font-size: 13px;">Menampilkan 0–0 dari 0
                    data
                </p>
                <div class="flex items-center gap-2">
                    <button id="prevPageMasterBpjs" class="btn-aksi" disabled><i class="ph ph-caret-left"></i></button>
                    <span id="pageInfoMasterBpjs" class="font-medium"
                        style="font-size: 14px; min-width: 100px; text-align: center;">1
                        /
                        1</span>
                    <button id="nextPageMasterBpjs" class="btn-aksi" disabled><i class="ph ph-caret-right"></i></button>
                </div>
            </div>
        </div>
    </div>

    {{-- DETAIL SECTION --}}
    <div id="detailListSectionBpjs" style="display: none; animation: fadeIn 0.3s ease-out;">
        <div class="dashboard-header" style="margin-bottom: 24px;">
            <div class="dashboard-header-left">
                <button onclick="closeDetailBpjs()"
                    style="display: inline-flex; align-items: center; gap: 8px; background: #fff; border: 1px solid #e2e8f0; padding: 8px 16px; border-radius: 10px; cursor: pointer; color: #64748b; font-weight: 600; margin-bottom: 12px; transition: all 0.2s;">
                    <i class="ph ph-arrow-left"></i> Kembali ke Daftar Kelompok
                </button>
                <h2><i class="ph ph-list-numbers"></i> Rincian Klaim BPJS</h2>
                <p style="font-size: 14px; color: #64748b;">Grup: <span id="detailMasterInfoBpjs"
                        style="font-weight: 700; color: #1e293b;">-</span></p>
            </div>

            <div class="dashboard-header-right">
                <div class="bpjs-tab-group tab-group">
                    <button class="bpjs-tab tab-item active" onclick="switchBpjsTab('REGULAR', this)" id="tabRegular">
                        <i class="ph ph-clipboard-text"></i> Regular
                    </button>
                    <button class="bpjs-tab tab-item" onclick="switchBpjsTab('EVAKUASI', this)" id="tabEvakuasi">
                        <i class="ph ph-ambulance"></i> Evakuasi
                    </button>
                    <button class="bpjs-tab tab-item" onclick="switchBpjsTab('OBAT', this)" id="tabObat">
                        <i class="ph ph-pill"></i> Obat
                    </button>
                </div>

                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                    <div class="toolbar-group" style="display: flex; gap: 8px;">
                        @if(auth()->user()->hasPermission('PENDAPATAN_BPJS_CREATE') || auth()->user()->hasPermission('PENDAPATAN_BPJS_CRUD'))
                            <a href="/dashboard/pendapatan/bpjs/template" class="btn-toolbar btn-toolbar-outline"
                                title="Download Template CSV"><i class="ph ph-download-simple"></i><span>Template</span></a>
                        @endif
                        @if(auth()->user()->hasPermission('PENDAPATAN_BPJS_CREATE') || auth()->user()->hasPermission('PENDAPATAN_BPJS_CRUD'))
                            <button class="btn-toolbar btn-toolbar-outline" id="btnImportBpjs"
                                title="Import Data dari CSV"><i class="ph ph-file-arrow-up"></i><span>Import</span></button>
                        @endif
                        @if(auth()->user()->hasPermission('PENDAPATAN_BPJS_DELETE') || auth()->user()->hasPermission('PENDAPATAN_BPJS_CRUD'))
                            <button class="btn-toolbar btn-toolbar-outline btn-bulk-delete" id="btnBulkDeleteBpjs"
                                title="Hapus Massal Rincian Pasien" style="color: #ef4444; border-color: #fca5a5;">
                                <i class="ph ph-trash"></i><span>Hapus Massal</span>
                            </button>
                        @endif
                    </div>
                    @if(auth()->user()->hasPermission('PENDAPATAN_BPJS_CREATE') || auth()->user()->hasPermission('PENDAPATAN_BPJS_CRUD'))
                        <button class="btn-tambah-data" id="btnTambahPendapatanBpjs"
                            style="background:#059669; height: 44px;" onclick="openPendapatanBpjsModal()">
                            <i class="ph-bold ph-plus"></i>
                            <span>Tambah Pasien</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- SUMMARY CARDS (DETAIL) --}}
        <div class="grid-responsive grid-3 mb-4" style="max-width: 850px; margin-inline: auto;">
            <div class="dash-card blue">
                <div class="dash-card-icon"><i class="ph ph-hospital"></i></div>
                <div class="dash-card-content">
                    <span class="label">Jasa Rumah Sakit</span>
                    <h3 id="detailSummaryRsBpjs">Rp 0</h3>
                </div>
            </div>
            <div class="dash-card purple">
                <div class="dash-card-icon"><i class="ph ph-user-gear"></i></div>
                <div class="dash-card-content">
                    <span class="label">Jasa Pelayanan</span>
                    <h3 id="detailSummaryPelayananBpjs">Rp 0</h3>
                </div>
            </div>
            <div class="dash-card green">
                <div class="dash-card-icon"><i class="ph ph-bank"></i></div>
                <div class="dash-card-content">
                    <span class="label">Total Klaim BPJS</span>
                    <h3 id="detailSummaryTotalBpjs" style="color: #16a34a;">Rp 0</h3>
                </div>
            </div>
        </div>

        <div class="dashboard-box" style="padding: 0;">
            <div class="table-toolbar" style="padding: 16px; margin-bottom: 0; border-bottom: 1px solid #f1f5f9;">
                <div class="table-search-wrapper" style="flex: 1;">
                    <i class="ph ph-magnifying-glass"></i>
                    <input type="text" id="searchPendapatanBpjs" class="table-search"
                        placeholder="Cari nama pasien, No SEP, ruangan..." data-table="pendapatanBpjsTable">
                </div>
            </div>
            <div class="table-container" style="margin-top: 0; border-radius: 0; border: none; max-height: 60vh;">
                <style>
                    #pendapatanBpjsTable th,
                    #pendapatanBpjsTable td {
                        font-size: 12px !important;
                    }

                    .nominal-group {
                        display: flex;
                        flex-direction: column;
                        align-items: stretch;
                        gap: 2px;
                        width: 100%;
                    }

                    .nom-row {
                        display: flex;
                        align-items: center;
                        gap: 8px;
                        width: 100%;
                    }

                    .nom-label {
                        font-size: 8px;
                        font-weight: 700;
                        padding: 1px 4px;
                        border-radius: 3px;
                        text-transform: uppercase;
                        width: 35px;
                        text-align: center;
                        flex-shrink: 0;
                    }

                    .nom-val {
                        font-family: 'JetBrains Mono', monospace;
                        flex-grow: 1;
                        font-size: 11px;
                    }

                    .label-rs {
                        background: #eff6ff;
                        color: #2563eb;
                    }

                    .label-pelayanan {
                        background: #fdf4ff;
                        color: #a21caf;
                    }

                    .label-total {
                        background: #ecfdf5;
                        color: #059669;
                    }

                    .val-rs {
                        font-weight: 600;
                        color: #2563eb;
                    }

                    .val-pelayanan {
                        font-weight: 600;
                        color: #a21caf;
                    }

                    .val-total {
                        font-weight: 800;
                        color: #059669;
                    }
                </style>
                <table id="pendapatanBpjsTable" class="universal-table table-mobile-cards">
                    <thead>
                        <tr>
                            <th class="text-center checkbox-col">No</th>
                            <th class="text-center sortable" onclick="sortBpjs('tanggal')" data-sort="tanggal">Tanggal
                                <i class="ph ph-caret-up-down"></i>
                            </th>
                            <th id="thNoSep" class="text-center sortable" onclick="sortBpjs('no_sep')"
                                data-sort="no_sep">No SEP <i class="ph ph-caret-up-down"></i></th>
                            <th class="text-center sortable" onclick="sortBpjs('nama_pasien')" data-sort="nama_pasien">
                                Nama Pasien <i class="ph ph-caret-up-down"></i></th>
                            <th class="text-center sortable" onclick="sortBpjs('ruangan_id')" data-sort="ruangan_id">
                                Ruangan <i class="ph ph-caret-up-down"></i></th>
                            <th class="text-right sortable" onclick="sortBpjs('total')" data-sort="total">RS / Pelayanan
                                / Total <i class="ph ph-caret-up-down"></i></th>
                            <th class="action-col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="pendapatanBpjsBody">
                        <tr>
                            <td colspan="7" class="text-center">Memuat rincian...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="flex justify-between items-center" style="padding: 16px;">
                <p id="paginationInfoBpjs" class="text-slate-500" style="font-size: 13px;">Menampilkan 0–0 dari 0 data
                </p>
                <div class="flex items-center gap-2">
                    <button id="prevPageBpjs" class="btn-aksi" disabled><i class="ph ph-caret-left"></i></button>
                    <span id="pageInfoBpjs" class="font-medium"
                        style="font-size: 14px; min-width: 100px; text-align: center;">1 /
                        1</span>
                    <button id="nextPageBpjs" class="btn-aksi" disabled><i class="ph ph-caret-right"></i></button>
                </div>
            </div>
        </div>
    </div>

</div>

@include('dashboard.partials.pendapatan-master-modals')
@include('dashboard.partials.pendapatan-bpjs-form')
@include('dashboard.partials.pendapatan-bpjs-detail')
@include('dashboard.partials.pendapatan-bpjs-import')

<style>
    .bpjs-tab.active {
        background: #fff !important;
        color: #2563eb !important;
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    }

    .bpjs-tab:not(.active) {
        background: transparent;
        color: #64748b;
    }

    .bpjs-tab:not(.active):hover {
        background: rgba(255, 255, 255, 0.5);
        color: #1e293b;
    }
</style>